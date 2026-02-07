<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Service for parsing and cleaning email content.
 *
 * Handles stripping quoted content, signatures, and normalizing email bodies.
 */
class EmailParserService
{
    /**
     * Common quote line patterns to detect email quotes.
     */
    protected array $quotePatterns = [
        // Standard "On X, Y wrote:" patterns in various languages
        '/^On\s+.+\s+wrote:?\s*$/im',
        '/^On\s+\w{3},\s+\w{3}\s+\d{1,2},\s+\d{4}\s+at\s+\d{1,2}:\d{2}.+wrote:?\s*$/im', // Gmail: "On Mon, Jan 1, 2024 at 10:00 AM ... wrote:"
        '/^On\s+\w{3},\s+\d{1,2}\s+\w{3}\s+\d{4}\s+at\s+\d{1,2}:\d{2}.+wrote:?\s*$/im', // Gmail variant: "On Mon, 1 Jan 2024 at 10:00 ... wrote:"
        '/^Am\s+.+\s+schrieb\s+.+:?\s*$/im', // German
        '/^Le\s+.+,\s+.+\s+a\s+écrit\s*:?\s*$/im', // French
        '/^El\s+.+,\s+.+\s+escribió\s*:?\s*$/im', // Spanish

        // Gmail patterns
        '/^-{2,}\s*Forwarded message\s*-{2,}$/im',
        '/^>{3}\s*.+$/m',

        // Outlook patterns
        '/^From:\s+.+$/im',
        '/^Sent:\s+.+$/im',
        '/^To:\s+.+$/im',
        '/^Subject:\s+.+$/im',

        // Generic dividers
        '/^-{3,}(\s*Original Message\s*)?-{3,}$/im',
        '/^_{3,}$/m',
        '/^\*{3,}$/m',

        // Email template markers (common in HTML emails)
        '/^(Lara Dashboard|Support Ticket|New Reply to Your Ticket)\s*$/im',
        '/^(Email|Email\s*\n\s*\n)\s*$/im',
        '/^Your Support Ticket Has/im',
        '/^Hello\s+(Customer|.+?),?\s*$/im',
        '/^Our support team has replied/im',
        '/^Thank you for contacting us/im',
    ];

    /**
     * Signature patterns to detect and strip email signatures.
     */
    protected array $signaturePatterns = [
        '/^--\s*$/m', // Standard signature delimiter
        '/^Sent from my\s+.+$/im',
        '/^Get Outlook for\s+.+$/im',
        '/^Enviado desde mi\s+.+$/im', // Spanish
        '/^Envoyé de mon\s+.+$/im', // French
        '/^Von meinem\s+.+\s+gesendet$/im', // German
        '/^Best\s+regards?,?$/im',
        '/^Kind\s+regards?,?$/im',
        '/^Thanks,?$/im',
        '/^Thank\s+you,?$/im',
        '/^Cheers,?$/im',
        '/^Regards,?$/im',
        '/^Sincerely,?$/im',
    ];

    /**
     * Parse email body and extract the actual reply content.
     *
     * @param string|null $body The raw email body
     * @param bool $stripSignature Whether to strip the signature
     * @return string|null The parsed reply content
     */
    public function parseReply(?string $body, bool $stripSignature = true): ?string
    {
        if (empty($body)) {
            return null;
        }

        // Normalize line endings
        $body = $this->normalizeLineEndings($body);

        // First try to find quote markers and cut there
        $body = $this->stripQuotedContent($body);

        // Strip signature if requested
        if ($stripSignature) {
            $body = $this->stripSignature($body);
        }

        // Clean up whitespace
        $body = $this->cleanWhitespace($body);

        return $body ?: null;
    }

    /**
     * Parse HTML email body and extract reply content.
     */
    public function parseHtmlReply(?string $html): ?string
    {
        if (empty($html)) {
            return null;
        }

        // Convert HTML to plain text
        $text = $this->htmlToText($html);

        // Parse as plain text
        return $this->parseReply($text);
    }

    /**
     * Strip quoted content from email body.
     */
    protected function stripQuotedContent(string $body): string
    {
        $lines = explode("\n", $body);
        $result = [];
        $inQuote = false;

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];

            // Check for quote start patterns
            foreach ($this->quotePatterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    $inQuote = true;
                    break 2; // Exit both loops
                }
            }

            // Check for Gmail-style multi-line quote header:
            // "On Sun, Feb 8, 2026 at 3:24 AM Name <email@example.com>
            // wrote:"
            if (preg_match('/^On\s+\w{3},\s+.+<[^>]+>\s*$/i', $line)) {
                // Check if next line is "wrote:" or similar
                $nextLine = $lines[$i + 1] ?? '';
                if (preg_match('/^wrote:?\s*$/i', trim($nextLine))) {
                    $inQuote = true;
                    break;
                }
            }

            // Check for lines starting with > (quoted text)
            if (preg_match('/^>+\s*/', $line)) {
                // Stop at quoted content
                break;
            }

            if (! $inQuote) {
                $result[] = $line;
            }
        }

        return implode("\n", $result);
    }

    /**
     * Strip email signature from body.
     */
    protected function stripSignature(string $body): string
    {
        $lines = explode("\n", $body);
        $resultLines = [];

        foreach ($lines as $line) {
            // Check for signature patterns
            foreach ($this->signaturePatterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    // Found signature start - include content up to here but not including
                    return implode("\n", $resultLines);
                }
            }

            $resultLines[] = $line;
        }

        return implode("\n", $resultLines);
    }

    /**
     * Normalize line endings to \n.
     */
    protected function normalizeLineEndings(string $text): string
    {
        return str_replace(["\r\n", "\r"], "\n", $text);
    }

    /**
     * Clean up excessive whitespace.
     */
    protected function cleanWhitespace(string $text): string
    {
        // Remove excessive blank lines (more than 2 in a row)
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        // Trim each line
        $lines = array_map('trim', explode("\n", $text));
        $text = implode("\n", $lines);

        // Trim overall
        return trim($text);
    }

    /**
     * Convert HTML to plain text.
     */
    protected function htmlToText(string $html): string
    {
        // Remove style and script tags with their content
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
        $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);

        // Handle common Gmail quote divs - remove everything from gmail_quote onwards
        $html = preg_replace('/<div[^>]*class="[^"]*gmail_quote[^"]*"[^>]*>.*$/is', '', $html);

        // Handle Gmail quote citation - "On ... wrote:"
        $html = preg_replace('/<div[^>]*class="[^"]*gmail_attr[^"]*"[^>]*>.*?<\/div>/is', '', $html);

        // Handle Outlook quote divs
        $html = preg_replace('/<div[^>]*id="appendonsend"[^>]*>.*$/is', '', $html);
        $html = preg_replace('/<div[^>]*id="divRplyFwdMsg"[^>]*>.*$/is', '', $html);

        // Handle blockquotes (often used for quotes)
        $html = preg_replace('/<blockquote[^>]*>.*$/is', '', $html);

        // Remove tables entirely (often part of email templates)
        $html = preg_replace('/<table[^>]*>.*?<\/table>/is', '', $html);

        // Remove images (logos, etc.)
        $html = preg_replace('/<img[^>]*>/is', '', $html);

        // Convert br and p tags to newlines
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $html = preg_replace('/<\/p>/i', "\n\n", $html);
        $html = preg_replace('/<p[^>]*>/i', '', $html);

        // Convert div closings to newlines
        $html = preg_replace('/<\/div>/i', "\n", $html);

        // Strip remaining HTML tags
        $text = strip_tags($html);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $text;
    }

    /**
     * Extract email addresses from a string.
     *
     * @return array<int, string>
     */
    public function extractEmailAddresses(string $text): array
    {
        preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches);

        return array_unique($matches[0] ?? []);
    }

    /**
     * Clean and normalize an email address.
     */
    public function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    /**
     * Extract name from "Name <email@example.com>" format.
     */
    public function extractNameFromAddress(string $address): ?string
    {
        // Match "Name <email>" format
        if (preg_match('/^(.+?)\s*<[^>]+>/', $address, $matches)) {
            $name = trim($matches[1], " \t\n\r\0\x0B\"'");

            return $name ?: null;
        }

        return null;
    }
}
