<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Trait HasQueueFallback
 *
 * Provides queue dispatching with automatic fallback to synchronous execution
 * when the queue worker is not running.
 *
 * Usage in a job class:
 *   use App\Concerns\HasQueueFallback;
 *
 *   class MyJob implements ShouldQueue
 *   {
 *       use HasQueueFallback;
 *       // ... rest of job
 *   }
 *
 * Then dispatch using:
 *   MyJob::dispatchWithFallback($arg1, $arg2);
 */
trait HasQueueFallback
{
    /**
     * Dispatch with fallback - uses queue if worker is running, otherwise executes immediately.
     *
     * @param  mixed  ...$arguments  Arguments to pass to the job constructor
     */
    public static function dispatchWithFallback(...$arguments): void
    {
        $queueConnection = config('queue.default');

        // If sync connection, just dispatch normally (it runs immediately)
        if ($queueConnection === 'sync') {
            static::dispatch(...$arguments);

            return;
        }

        // For database queue, check if worker is running by looking for stale jobs
        if ($queueConnection === 'database' && static::isQueueWorkerStale()) {
            Log::info('Queue worker appears inactive, dispatching job synchronously', [
                'job' => static::class,
            ]);
            static::dispatchSync(...$arguments);

            return;
        }

        // For redis queue, we could add similar check using Redis commands if needed
        // For now, assume redis workers are running if redis is configured

        // Queue seems to be working, dispatch normally
        static::dispatch(...$arguments);
    }

    /**
     * Check if queue worker appears to be stale (not processing jobs).
     *
     * @param  int  $staleSeconds  Consider jobs stale after this many seconds
     */
    protected static function isQueueWorkerStale(int $staleSeconds = 30): bool
    {
        try {
            $queueConnection = config('queue.default');

            if ($queueConnection === 'database') {
                return static::isDatabaseQueueStale($staleSeconds);
            }

            // For other connections (redis, sqs, etc.), assume they're working
            return false;
        } catch (\Exception $e) {
            Log::warning('Failed to check queue worker status', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if database queue has stale (unprocessed) jobs.
     *
     * A job is considered stale if:
     * - It has 0 attempts (never been picked up by a worker)
     * - It was created more than $staleSeconds ago
     *
     * Also returns true if there are multiple pending jobs (indicating worker is behind or not running).
     */
    protected static function isDatabaseQueueStale(int $staleSeconds): bool
    {
        // Check for old unprocessed jobs
        $staleJobsCount = DB::table('jobs')
            ->where('attempts', 0)
            ->where('created_at', '<', now()->subSeconds($staleSeconds)->timestamp)
            ->count();

        if ($staleJobsCount > 0) {
            return true;
        }

        // Also check if there are multiple pending jobs - indicates worker might not be running
        // A healthy worker should process jobs quickly, so more than 2 pending jobs suggests issues
        $pendingJobsCount = DB::table('jobs')
            ->where('attempts', 0)
            ->count();

        return $pendingJobsCount >= 2;
    }
}
