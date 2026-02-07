<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\InboundEmailHandlerInterface;
use App\Enums\InboundEmailHook;
use App\Models\InboundEmail;
use App\Models\InboundEmailConnection;
use App\Support\Facades\Hook;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Main processor for inbound emails.
 *
 * Fetches emails via IMAP, parses them, and dispatches to registered handlers.
 */
class InboundEmailProcessor
{
    /**
     * @var Collection<int, InboundEmailHandlerInterface>
     */
    protected Collection $handlers;

    public function __construct(
        protected ImapService $imapService,
        protected EmailParserService $emailParser,
    ) {
        $this->handlers = collect();
    }

    /**
     * Register an email handler.
     */
    public function registerHandler(InboundEmailHandlerInterface $handler): self
    {
        $this->handlers->push($handler);

        // Sort by priority (lower = higher priority)
        $this->handlers = $this->handlers->sortBy(fn ($h) => $h->getPriority());

        return $this;
    }

    /**
     * Get all registered handlers.
     *
     * @return Collection<int, InboundEmailHandlerInterface>
     */
    public function getHandlers(): Collection
    {
        return $this->handlers;
    }

    /**
     * Process emails from a specific connection.
     *
     * @param  bool  $fetchRecent  Fetch recent emails instead of unread only (useful for "All Mail" folders)
     * @param  int  $daysBack  Days to look back when fetching recent emails
     * @return array{fetched: int, processed: int, failed: int, errors: array}
     */
    public function processConnection(InboundEmailConnection $connection, bool $fetchRecent = false, int $daysBack = 7): array
    {
        $stats = [
            'fetched' => 0,
            'processed' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        try {
            // Connect to IMAP
            $this->imapService->connect($connection);

            // Fetch emails - recent or unread based on option
            if ($fetchRecent) {
                $emails = $this->imapService->fetchRecentEmails($connection->fetch_limit, $daysBack);
            } else {
                $emails = $this->imapService->fetchUnreadEmails($connection->fetch_limit);
            }

            // Filter out emails already imported (by message_id)
            $emails = $this->filterAlreadyImportedEmails($emails);
            $stats['fetched'] = count($emails);

            foreach ($emails as $emailData) {
                try {
                    // Create inbound email record
                    $inboundEmail = $this->createInboundEmail($emailData, $connection);

                    // Process the email
                    $result = $this->processEmail($inboundEmail);

                    if ($result) {
                        $stats['processed']++;

                        // Mark as read in IMAP if configured
                        if ($connection->mark_as_read) {
                            $this->imapService->markAsRead($emailData['imap_uid']);
                        }

                        // Delete from IMAP if configured
                        if ($connection->delete_after_processing) {
                            $this->imapService->deleteEmail($emailData['imap_uid']);
                        }
                    } else {
                        $stats['failed']++;
                    }
                } catch (\Throwable $e) {
                    $stats['failed']++;
                    $stats['errors'][] = $e->getMessage();

                    Log::error('Failed to process inbound email', [
                        'connection_id' => $connection->id,
                        'message_id' => $emailData['message_id'] ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update connection stats
            $connection->markAsChecked(true, sprintf(
                'Fetched %d, processed %d, failed %d',
                $stats['fetched'],
                $stats['processed'],
                $stats['failed']
            ));

            $connection->incrementProcessedCount($stats['processed']);

        } catch (\Throwable $e) {
            $connection->markAsChecked(false, $e->getMessage());
            $stats['errors'][] = $e->getMessage();

            Log::error('Failed to process inbound email connection', [
                'connection_id' => $connection->id,
                'error' => $e->getMessage(),
            ]);
        } finally {
            $this->imapService->disconnect();
        }

        return $stats;
    }

    /**
     * Process all active connections due for polling.
     *
     * @return array<int, array{connection_id: int, stats: array}>
     */
    public function processAllDueConnections(): array
    {
        $results = [];

        $connections = InboundEmailConnection::query()
            ->dueForPolling()
            ->get();

        foreach ($connections as $connection) {
            $stats = $this->processConnection($connection);
            $results[] = [
                'connection_id' => $connection->id,
                'connection_name' => $connection->name,
                'stats' => $stats,
            ];
        }

        return $results;
    }

    /**
     * Create an InboundEmail record from fetched data.
     */
    protected function createInboundEmail(array $emailData, InboundEmailConnection $connection): InboundEmail
    {
        // Parse the body to get the actual reply content
        $bodyParsed = null;
        if (! empty($emailData['body_plain'])) {
            $bodyParsed = $this->emailParser->parseReply($emailData['body_plain']);
        } elseif (! empty($emailData['body_html'])) {
            $bodyParsed = $this->emailParser->parseHtmlReply($emailData['body_html']);
        }

        return InboundEmail::create([
            'inbound_email_connection_id' => $connection->id,
            'message_id' => $emailData['message_id'],
            'in_reply_to' => $emailData['in_reply_to'],
            'references' => $emailData['references'],
            'from_email' => $emailData['from_email'],
            'from_name' => $emailData['from_name'],
            'to_email' => $emailData['to_email'],
            'to_name' => $emailData['to_name'],
            'cc' => $emailData['cc'],
            'subject' => $emailData['subject'],
            'email_date' => $emailData['email_date'],
            'body_plain' => $emailData['body_plain'],
            'body_html' => $emailData['body_html'],
            'body_parsed' => $bodyParsed,
            'attachments' => $emailData['attachments'],
            'raw_headers' => $emailData['raw_headers'],
            'imap_uid' => $emailData['imap_uid'],
            'status' => InboundEmail::STATUS_PENDING,
        ]);
    }

    /**
     * Process a single inbound email through handlers.
     */
    public function processEmail(InboundEmail $email): bool
    {
        // Fire before process hook
        Hook::doAction(InboundEmailHook::BEFORE_PROCESS, $email);

        $email->markAsProcessing();

        try {
            // Find a handler that can process this email
            $handler = $this->findHandler($email);

            if ($handler === null) {
                // No handler matched
                Hook::doAction(InboundEmailHook::UNMATCHED, $email);

                $email->markAsFailed('No handler matched this email');

                return false;
            }

            // Process with the matched handler
            $result = $handler->handle($email);

            if ($result->isSuccess()) {
                $email->markAsProcessed(
                    $handler->getHandlerType(),
                    $result->modelType,
                    $result->modelId
                );

                // Fire after process hook
                Hook::doAction(InboundEmailHook::AFTER_PROCESS, $email, $handler, $result);

                return true;
            }

            // Handler returned failure
            $email->markAsFailed($result->message ?? 'Handler returned failure');

            Hook::doAction(InboundEmailHook::PROCESS_FAILED, $email, $result->message);

            return false;

        } catch (\Throwable $e) {
            $email->markAsFailed($e->getMessage());

            Hook::doAction(InboundEmailHook::PROCESS_FAILED, $email, $e->getMessage());

            Log::error('Exception while processing inbound email', [
                'email_id' => $email->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Find a handler that can process the email.
     */
    protected function findHandler(InboundEmail $email): ?InboundEmailHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($email)) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * Reprocess a failed or pending email.
     */
    public function reprocessEmail(InboundEmail $email): bool
    {
        $email->update([
            'status' => InboundEmail::STATUS_PENDING,
            'processing_error' => null,
        ]);

        return $this->processEmail($email);
    }

    /**
     * Get handler statistics.
     *
     * @return array<string, array{name: string, priority: int}>
     */
    public function getHandlerStats(): array
    {
        $stats = [];

        foreach ($this->handlers as $handler) {
            $stats[$handler->getHandlerType()] = [
                'name' => $handler->getName(),
                'priority' => $handler->getPriority(),
            ];
        }

        return $stats;
    }

    /**
     * Filter out emails that have already been imported.
     *
     * @param  array<int, array>  $emails
     * @return array<int, array>
     */
    protected function filterAlreadyImportedEmails(array $emails): array
    {
        if (empty($emails)) {
            return [];
        }

        // Get all message IDs from the fetched emails
        $messageIds = array_filter(array_column($emails, 'message_id'));

        if (empty($messageIds)) {
            return $emails;
        }

        // Find which message IDs already exist in the database
        $existingMessageIds = InboundEmail::query()
            ->whereIn('message_id', $messageIds)
            ->pluck('message_id')
            ->toArray();

        if (empty($existingMessageIds)) {
            return $emails;
        }

        // Filter out already imported emails
        return array_filter($emails, function ($email) use ($existingMessageIds) {
            return ! in_array($email['message_id'] ?? null, $existingMessageIds, true);
        });
    }
}
