<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\InboundEmailConnection;
use App\Services\InboundEmailProcessor;
use Illuminate\Console\Command;

class ProcessInboundEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:process-inbound
                            {--connection= : Process a specific connection by ID or UUID}
                            {--all : Process all active connections regardless of polling interval}
                            {--test : Test connection(s) without processing emails}
                            {--recent : Fetch recent emails instead of unread only (useful for All Mail folders)}
                            {--days=7 : Number of days to look back when using --recent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process inbound emails from configured IMAP connections';

    /**
     * Execute the console command.
     */
    public function handle(InboundEmailProcessor $processor): int
    {
        if ($this->option('test')) {
            return $this->testConnections();
        }

        $connectionOption = $this->option('connection');

        if ($connectionOption) {
            return $this->processSpecificConnection($processor, $connectionOption);
        }

        if ($this->option('all')) {
            return $this->processAllConnections($processor);
        }

        return $this->processDueConnections($processor);
    }

    /**
     * Process connections that are due for polling.
     */
    protected function processDueConnections(InboundEmailProcessor $processor): int
    {
        $this->info('Processing inbound email connections due for polling...');

        $results = $processor->processAllDueConnections();

        if (empty($results)) {
            $this->info('No connections due for polling.');

            return self::SUCCESS;
        }

        $this->displayResults($results);

        return $this->hasErrors($results) ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Process all active connections.
     */
    protected function processAllConnections(InboundEmailProcessor $processor): int
    {
        $fetchRecent = $this->option('recent');
        $daysBack = (int) $this->option('days');

        if ($fetchRecent) {
            $this->info("Processing all active inbound email connections (fetching recent emails from last {$daysBack} days)...");
        } else {
            $this->info('Processing all active inbound email connections...');
        }

        $connections = InboundEmailConnection::query()
            ->active()
            ->get();

        if ($connections->isEmpty()) {
            $this->warn('No active inbound email connections found.');

            return self::SUCCESS;
        }

        $results = [];
        foreach ($connections as $connection) {
            $stats = $processor->processConnection($connection, $fetchRecent, $daysBack);
            $results[] = [
                'connection_id' => $connection->id,
                'connection_name' => $connection->name,
                'stats' => $stats,
            ];
        }

        $this->displayResults($results);

        return $this->hasErrors($results) ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Process a specific connection.
     */
    protected function processSpecificConnection(InboundEmailProcessor $processor, string $identifier): int
    {
        $connection = InboundEmailConnection::query()
            ->where('id', $identifier)
            ->orWhere('uuid', $identifier)
            ->first();

        if (! $connection) {
            $this->error("Connection not found: {$identifier}");

            return self::FAILURE;
        }

        $fetchRecent = $this->option('recent');
        $daysBack = (int) $this->option('days');

        if ($fetchRecent) {
            $this->info("Processing connection: {$connection->name} (fetching recent emails from last {$daysBack} days)");
        } else {
            $this->info("Processing connection: {$connection->name}");
        }

        $stats = $processor->processConnection($connection, $fetchRecent, $daysBack);

        $this->displayResults([
            [
                'connection_id' => $connection->id,
                'connection_name' => $connection->name,
                'stats' => $stats,
            ],
        ]);

        return ! empty($stats['errors']) ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Test IMAP connections without processing.
     */
    protected function testConnections(): int
    {
        $connectionOption = $this->option('connection');

        if ($connectionOption) {
            $connections = InboundEmailConnection::query()
                ->where('id', $connectionOption)
                ->orWhere('uuid', $connectionOption)
                ->get();
        } else {
            $connections = InboundEmailConnection::query()->active()->get();
        }

        if ($connections->isEmpty()) {
            $this->warn('No connections found to test.');

            return self::SUCCESS;
        }

        $imapService = app(\App\Services\ImapService::class);
        $hasFailures = false;

        foreach ($connections as $connection) {
            $this->info("Testing connection: {$connection->name}...");

            $result = $imapService->testConnection($connection);

            if ($result['success']) {
                $this->info("  ✓ {$result['message']}");
                $connection->markAsChecked(true, $result['message']);
            } else {
                $this->error("  ✗ {$result['message']}");
                $connection->markAsChecked(false, $result['message']);
                $hasFailures = true;
            }
        }

        return $hasFailures ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Display processing results in a table.
     */
    protected function displayResults(array $results): void
    {
        $rows = [];
        foreach ($results as $result) {
            $stats = $result['stats'];
            $errors = ! empty($stats['errors']) ? implode('; ', array_slice($stats['errors'], 0, 2)) : '-';

            $rows[] = [
                $result['connection_id'],
                $result['connection_name'],
                $stats['fetched'],
                $stats['processed'],
                $stats['failed'],
                strlen($errors) > 50 ? substr($errors, 0, 47).'...' : $errors,
            ];
        }

        $this->table(
            ['ID', 'Name', 'Fetched', 'Processed', 'Failed', 'Errors'],
            $rows
        );

        // Summary
        $totalFetched = array_sum(array_column(array_column($results, 'stats'), 'fetched'));
        $totalProcessed = array_sum(array_column(array_column($results, 'stats'), 'processed'));
        $totalFailed = array_sum(array_column(array_column($results, 'stats'), 'failed'));

        $this->newLine();
        $this->info("Summary: Fetched {$totalFetched}, Processed {$totalProcessed}, Failed {$totalFailed}");
    }

    /**
     * Check if any results have errors.
     */
    protected function hasErrors(array $results): bool
    {
        foreach ($results as $result) {
            if (! empty($result['stats']['errors'])) {
                return true;
            }
        }

        return false;
    }
}
