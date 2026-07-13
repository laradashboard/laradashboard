<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ErrorNotificationLog;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ErrorLogNotificationService
{
    private const SEVERITIES = ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

    /**
     * Match a Laravel log header line like:
     *   [2026-05-17 00:44:23] production.ERROR: Failed to authenticate ...
     */
    private const HEADER_REGEX = '/^\[(?<datetime>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] [^.]+\.(?<level>[A-Z]+): (?<message>.*)$/';

    /**
     * Default log path.
     */
    public function defaultLogPath(): string
    {
        return storage_path('logs/laravel.log');
    }

    /**
     * Parse the given log file for ERROR-class entries within the given window,
     * dedupe by content hash, persist new ones, and return only the records
     * that have NOT been notified before (so each unique error notifies once).
     *
     * @return Collection<int, ErrorNotificationLog>
     */
    public function collectUnnotifiedErrors(?string $logPath = null, ?int $windowHours = 24): Collection
    {
        $path = $logPath ?? $this->defaultLogPath();

        if (! is_file($path) || ! is_readable($path)) {
            return collect();
        }

        $cutoff = $windowHours
            ? CarbonImmutable::now()->subHours($windowHours)
            : null;

        $grouped = $this->parseEntries($path, $cutoff);

        if ($grouped->isEmpty()) {
            return collect();
        }

        return DB::transaction(function () use ($grouped) {
            $touchedIds = [];

            foreach ($grouped as $hash => $entry) {
                $log = ErrorNotificationLog::where('error_hash', $hash)->first();

                if ($log === null) {
                    $log = ErrorNotificationLog::create([
                        'error_hash' => $hash,
                        'level' => $entry['level'],
                        'message' => $entry['message'],
                        'file' => $entry['file'],
                        'line' => $entry['line'],
                        'occurrences' => $entry['count'],
                        'first_seen_at' => $entry['first_seen_at'],
                        'last_seen_at' => $entry['last_seen_at'],
                    ]);
                } else {
                    $log->update([
                        'occurrences' => $log->occurrences + $entry['count'],
                        'last_seen_at' => $entry['last_seen_at'],
                    ]);
                }

                $touchedIds[] = $log->id;
            }

            // "Unnotified" = recorded but never emailed about yet. This makes the
            // method idempotent: a dry-run records rows but a subsequent real run
            // can still pick them up because notified_at is still null.
            return ErrorNotificationLog::query()
                ->whereIn('id', $touchedIds)
                ->whereNull('notified_at')
                ->orderBy('id')
                ->get();
        });
    }

    /**
     * Mark a collection of error logs as notified at the given time.
     *
     * @param  Collection<int, ErrorNotificationLog>  $logs
     */
    public function markNotified(Collection $logs, ?CarbonImmutable $at = null): void
    {
        if ($logs->isEmpty()) {
            return;
        }

        ErrorNotificationLog::whereIn('id', $logs->pluck('id'))
            ->update(['notified_at' => $at ?? CarbonImmutable::now()]);
    }

    /**
     * Stream the log file, group ERROR-class entries by content hash.
     *
     * Each value is an array with keys: level, message, file, line, count,
     * first_seen_at, last_seen_at. Not expressed as a typed shape because
     * static analysis can't carry it through the `collect()` boundary.
     *
     * @return Collection<string, array<string, mixed>>
     */
    private function parseEntries(string $path, ?CarbonImmutable $cutoff): Collection
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return collect();
        }

        $grouped = [];
        $current = null;

        try {
            while (($line = fgets($handle)) !== false) {
                if (preg_match(self::HEADER_REGEX, $line, $m)) {
                    if ($current !== null) {
                        $this->absorb($grouped, $current);
                    }

                    $level = strtoupper($m['level']);
                    if (! in_array($level, self::SEVERITIES, true)) {
                        $current = null;
                        continue;
                    }

                    $datetime = CarbonImmutable::parse($m['datetime']);
                    if ($cutoff !== null && $datetime->lessThan($cutoff)) {
                        $current = null;
                        continue;
                    }

                    $current = [
                        'level' => $level,
                        'message' => trim($m['message']),
                        'datetime' => $datetime,
                        'tail' => '',
                    ];
                    continue;
                }

                if ($current !== null && strlen($current['tail']) < 2000) {
                    $current['tail'] .= $line;
                }
            }

            if ($current !== null) {
                $this->absorb($grouped, $current);
            }
        } finally {
            fclose($handle);
        }

        return collect($grouped);
    }

    /**
     * Merge a parsed entry into the grouped bag, keyed by content hash.
     *
     * @param  array<string, array<string, mixed>>  $grouped
     * @param  array{level: string, message: string, datetime: CarbonImmutable, tail: string}  $entry
     */
    private function absorb(array &$grouped, array $entry): void
    {
        [$file, $line] = $this->extractFileLine($entry['message'].' '.$entry['tail']);

        $signature = $this->signature($entry['message'], $file, $line);
        $hash = hash('sha256', $entry['level'].'|'.$signature);

        if (! isset($grouped[$hash])) {
            $grouped[$hash] = [
                'level' => $entry['level'],
                'message' => mb_substr($entry['message'], 0, 1000),
                'file' => $file,
                'line' => $line,
                'count' => 1,
                'first_seen_at' => $entry['datetime'],
                'last_seen_at' => $entry['datetime'],
            ];

            return;
        }

        $grouped[$hash]['count']++;
        if ($entry['datetime']->greaterThan($grouped[$hash]['last_seen_at'])) {
            $grouped[$hash]['last_seen_at'] = $entry['datetime'];
        }
        if ($entry['datetime']->lessThan($grouped[$hash]['first_seen_at'])) {
            $grouped[$hash]['first_seen_at'] = $entry['datetime'];
        }
    }

    /**
     * Build a signature that ignores volatile parts (timestamps, ids, hashes)
     * so the same root error groups even when details vary.
     */
    private function signature(string $message, ?string $file, ?int $line): string
    {
        $normalized = preg_replace('/\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}/', '<date>', $message);
        $normalized = preg_replace('/\b[0-9a-f]{32,}\b/i', '<hash>', (string) $normalized);
        $normalized = preg_replace('/\b\d{4,}\b/', '<num>', (string) $normalized);

        return trim((string) $normalized).'|'.($file ?? '').':'.($line ?? '');
    }

    /**
     * @return array{0: ?string, 1: ?int}
     */
    private function extractFileLine(string $haystack): array
    {
        if (preg_match('#(/[A-Za-z0-9._/\-]+\.php):(\d+)#', $haystack, $m)) {
            return [$m[1], (int) $m[2]];
        }

        return [null, null];
    }
}
