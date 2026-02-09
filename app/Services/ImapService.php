<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\InboundEmail;
use App\Models\InboundEmailConnection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service for fetching emails via IMAP.
 *
 * Uses PHP's native IMAP extension.
 */
class ImapService
{
    private mixed $connection = null;

    private ?InboundEmailConnection $model = null;

    /**
     * Connect to an IMAP server.
     *
     * @throws \RuntimeException If connection fails
     */
    public function connect(InboundEmailConnection $connectionModel): self
    {
        $this->model = $connectionModel;

        if (! extension_loaded('imap')) {
            throw new \RuntimeException('PHP IMAP extension is not installed.');
        }

        $connectionString = $connectionModel->getImapConnectionString();

        // Suppress warnings during connection attempt
        $this->connection = @imap_open(
            $connectionString,
            $connectionModel->imap_username,
            $connectionModel->imap_password,
            0,
            1
        );

        if ($this->connection === false) {
            $errors = imap_errors();
            $lastError = imap_last_error();
            throw new \RuntimeException(
                'Failed to connect to IMAP server: '.($lastError ?: implode(', ', $errors ?: ['Unknown error']))
            );
        }

        return $this;
    }

    /**
     * Test the IMAP connection.
     *
     * @return array{success: bool, message: string}
     */
    public function testConnection(InboundEmailConnection $connectionModel): array
    {
        try {
            $this->connect($connectionModel);
            $mailboxInfo = imap_check($this->connection);
            $this->disconnect();

            return [
                'success' => true,
                'message' => sprintf(
                    'Connected successfully. Mailbox: %s, Messages: %d',
                    $mailboxInfo->Mailbox ?? 'Unknown',
                    $mailboxInfo->Nmsgs ?? 0
                ),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch unread/unseen emails from the mailbox.
     *
     * @return array<int, array> Array of email data
     */
    public function fetchUnreadEmails(int $limit = 50): array
    {
        $this->ensureConnected();

        // Search for unseen (unread) messages
        $messageIds = imap_search($this->connection, 'UNSEEN');

        if ($messageIds === false) {
            return [];
        }

        // Limit the number of emails to process
        $messageIds = array_slice($messageIds, 0, $limit);

        $emails = [];
        foreach ($messageIds as $messageId) {
            try {
                $emails[] = $this->fetchEmailDetails($messageId);
            } catch (\Throwable $e) {
                Log::warning('Failed to fetch email details', [
                    'message_id' => $messageId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $emails;
    }

    /**
     * Fetch recent emails from the mailbox (newest first).
     *
     * This method fetches the most recent emails regardless of read status,
     * useful for folders like Gmail's "All Mail" where emails may already be read.
     *
     * @param  int  $limit  Maximum number of emails to fetch
     * @param  int  $daysBack  Only fetch emails from the last N days (0 = all)
     * @return array<int, array> Array of email data
     */
    public function fetchRecentEmails(int $limit = 50, int $daysBack = 7): array
    {
        $this->ensureConnected();

        // Build search criteria
        $searchCriteria = 'ALL';
        if ($daysBack > 0) {
            $since = Carbon::now()->subDays($daysBack)->format('j-M-Y');
            $searchCriteria = 'SINCE "'.$since.'"';
        }

        $messageIds = imap_search($this->connection, $searchCriteria);

        if ($messageIds === false) {
            return [];
        }

        // Sort by message ID descending (newest first)
        rsort($messageIds);

        // Limit the number of emails to process
        $messageIds = array_slice($messageIds, 0, $limit);

        $emails = [];
        foreach ($messageIds as $messageId) {
            try {
                $emails[] = $this->fetchEmailDetails($messageId);
            } catch (\Throwable $e) {
                Log::warning('Failed to fetch email details', [
                    'message_id' => $messageId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $emails;
    }

    /**
     * Fetch details of a single email.
     */
    protected function fetchEmailDetails(int $messageId): array
    {
        $this->ensureConnected();

        $header = imap_headerinfo($this->connection, $messageId);
        $structure = imap_fetchstructure($this->connection, $messageId);
        $rawHeaders = imap_fetchheader($this->connection, $messageId);
        $uid = imap_uid($this->connection, $messageId);

        // Parse the body
        $body = $this->parseBody($messageId, $structure);

        // Parse important headers
        $messageIdHeader = $this->extractHeader($rawHeaders, 'Message-ID');
        $inReplyTo = $this->extractHeader($rawHeaders, 'In-Reply-To');
        $references = $this->extractHeader($rawHeaders, 'References');

        // Parse From address
        $from = $header->from[0] ?? null;
        $fromEmail = $from ? ($from->mailbox.'@'.$from->host) : '';
        $fromName = isset($from->personal) ? $this->decodeHeader($from->personal) : null;

        // Parse To address
        $to = $header->to[0] ?? null;
        $toEmail = $to ? ($to->mailbox.'@'.$to->host) : null;
        $toName = isset($to->personal) ? $this->decodeHeader($to->personal) : null;

        // Parse CC addresses
        $cc = [];
        if (isset($header->cc)) {
            foreach ($header->cc as $ccAddr) {
                $cc[] = [
                    'email' => $ccAddr->mailbox.'@'.$ccAddr->host,
                    'name' => isset($ccAddr->personal) ? $this->decodeHeader($ccAddr->personal) : null,
                ];
            }
        }

        // Parse date
        $emailDate = null;
        if (isset($header->date)) {
            try {
                $emailDate = Carbon::parse($header->date);
            } catch (\Throwable) {
                $emailDate = null;
            }
        }

        return [
            'imap_uid' => (string) $uid,
            'imap_message_id' => $messageId,
            'message_id' => $messageIdHeader,
            'in_reply_to' => $inReplyTo,
            'references' => $references,
            'from_email' => strtolower($fromEmail),
            'from_name' => $fromName,
            'to_email' => $toEmail ? strtolower($toEmail) : null,
            'to_name' => $toName,
            'cc' => ! empty($cc) ? $cc : null,
            'subject' => isset($header->subject) ? $this->decodeHeader($header->subject) : null,
            'email_date' => $emailDate,
            'body_plain' => $body['plain'] ?? null,
            'body_html' => $body['html'] ?? null,
            'attachments' => $body['attachments'] ?? null,
            'raw_headers' => $rawHeaders,
        ];
    }

    /**
     * Parse the email body (plain text and HTML).
     */
    protected function parseBody(int $messageId, object $structure): array
    {
        $result = [
            'plain' => null,
            'html' => null,
            'attachments' => [],
        ];

        // Simple message (not multipart)
        if (empty($structure->parts)) {
            $body = imap_body($this->connection, $messageId);
            $decoded = $this->decodeBody($body, $structure);

            if ($structure->subtype === 'PLAIN') {
                $result['plain'] = $decoded;
            } elseif ($structure->subtype === 'HTML') {
                $result['html'] = $decoded;
            }

            return $result;
        }

        // Multipart message
        foreach ($structure->parts as $partIndex => $part) {
            $this->parsePart($messageId, $part, $partIndex + 1, $result);
        }

        return $result;
    }

    /**
     * Parse a single part of a multipart message.
     */
    protected function parsePart(int $messageId, object $part, int|string $partNumber, array &$result): void
    {
        // Check if this is an attachment
        $isAttachment = false;
        $filename = null;

        // Check disposition
        if (isset($part->disposition) && strtoupper($part->disposition) === 'ATTACHMENT') {
            $isAttachment = true;
            if (isset($part->dparameters)) {
                foreach ($part->dparameters as $param) {
                    if (strtolower($param->attribute) === 'filename') {
                        $filename = $this->decodeHeader($param->value);
                    }
                }
            }
        }

        // Check parameters for filename
        if ($filename === null && isset($part->parameters)) {
            foreach ($part->parameters as $param) {
                if (strtolower($param->attribute) === 'name') {
                    $filename = $this->decodeHeader($param->value);
                    $isAttachment = true;
                }
            }
        }

        // Handle attachment
        if ($isAttachment && $filename) {
            $result['attachments'][] = [
                'filename' => $filename,
                'type' => $part->subtype ?? 'unknown',
                'size' => $part->bytes ?? 0,
                'part_number' => $partNumber,
            ];

            return;
        }

        // Handle text parts
        $body = imap_fetchbody($this->connection, $messageId, (string) $partNumber);
        $decoded = $this->decodeBody($body, $part);

        if ($part->type === 0) { // Text
            if (isset($part->subtype) && strtoupper($part->subtype) === 'PLAIN' && $result['plain'] === null) {
                $result['plain'] = $decoded;
            } elseif (isset($part->subtype) && strtoupper($part->subtype) === 'HTML' && $result['html'] === null) {
                $result['html'] = $decoded;
            }
        }

        // Handle nested multipart
        if ($part->type === 1 && ! empty($part->parts)) { // Multipart
            foreach ($part->parts as $subIndex => $subPart) {
                $this->parsePart($messageId, $subPart, $partNumber.'.'.($subIndex + 1), $result);
            }
        }
    }

    /**
     * Decode body content based on encoding.
     */
    protected function decodeBody(string $body, object $structure): string
    {
        // Handle encoding
        $encoding = $structure->encoding ?? 0;
        switch ($encoding) {
            case 3: // BASE64
                $body = base64_decode($body);
                break;
            case 4: // QUOTED-PRINTABLE
                $body = quoted_printable_decode($body);
                break;
        }

        // Handle charset conversion
        $charset = 'UTF-8';
        if (isset($structure->parameters)) {
            foreach ($structure->parameters as $param) {
                if (strtolower($param->attribute) === 'charset') {
                    $charset = $param->value;
                    break;
                }
            }
        }

        if (strtoupper($charset) !== 'UTF-8') {
            $converted = @iconv($charset, 'UTF-8//IGNORE', $body);
            if ($converted !== false) {
                $body = $converted;
            }
        }

        return $body;
    }

    /**
     * Extract a specific header from raw headers.
     */
    protected function extractHeader(string $rawHeaders, string $headerName): ?string
    {
        $pattern = '/^'.preg_quote($headerName, '/').':\s*(.+?)(?=\r?\n[^\s]|\r?\n$)/mi';
        if (preg_match($pattern, $rawHeaders, $matches)) {
            // Handle multi-line headers
            $value = preg_replace('/\r?\n\s+/', ' ', trim($matches[1]));

            return $value;
        }

        return null;
    }

    /**
     * Decode MIME encoded header.
     */
    protected function decodeHeader(string $header): string
    {
        $decoded = imap_mime_header_decode($header);
        $result = '';

        foreach ($decoded as $element) {
            $charset = $element->charset;
            $text = $element->text;

            if ($charset !== 'default' && $charset !== 'UTF-8') {
                $converted = @iconv($charset, 'UTF-8//IGNORE', $text);
                if ($converted !== false) {
                    $text = $converted;
                }
            }

            $result .= $text;
        }

        return $result;
    }

    /**
     * Mark an email as read/seen.
     */
    public function markAsRead(string $uid): void
    {
        $this->ensureConnected();
        imap_setflag_full($this->connection, $uid, '\\Seen', ST_UID);
    }

    /**
     * Delete an email.
     */
    public function deleteEmail(string $uid): void
    {
        $this->ensureConnected();
        imap_delete($this->connection, $uid, FT_UID);
        imap_expunge($this->connection);
    }

    /**
     * Disconnect from the IMAP server.
     */
    public function disconnect(): void
    {
        if ($this->connection !== null && $this->connection !== false) {
            imap_close($this->connection);
        }
        $this->connection = null;
        $this->model = null;
    }

    /**
     * Ensure we have an active connection.
     */
    protected function ensureConnected(): void
    {
        if ($this->connection === null || $this->connection === false) {
            throw new \RuntimeException('Not connected to IMAP server. Call connect() first.');
        }
    }

    /**
     * Get the connection model.
     */
    public function getModel(): ?InboundEmailConnection
    {
        return $this->model;
    }

    /**
     * Create InboundEmail record from fetched email data.
     */
    public function createInboundEmail(array $emailData, InboundEmailConnection $connection): InboundEmail
    {
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
            'attachments' => $emailData['attachments'],
            'raw_headers' => $emailData['raw_headers'],
            'imap_uid' => $emailData['imap_uid'],
            'status' => InboundEmail::STATUS_PENDING,
        ]);
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
