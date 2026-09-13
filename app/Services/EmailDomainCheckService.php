<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Hooks\CommonFilterHook;
use App\Support\Facades\Hook;

/**
 * Free, no-API-key email domain checks shared by any feature that needs to
 * reject disposable/undeliverable email addresses (registration, contact
 * forms, etc.). Used standalone, or as the fallback when a paid verification
 * service (see EmailVerificationService) is disabled or out of quota.
 */
class EmailDomainCheckService
{
    /**
     * Well-known disposable/throwaway email domains. Extend via the
     * REGISTRATION_DISPOSABLE_EMAIL_DOMAINS hook instead of editing this list.
     *
     * @var list<string>
     */
    private const DISPOSABLE_EMAIL_DOMAINS = [
        'mailinator.com', 'mailinator.net', 'mailinator.org',
        'guerrillamail.com', 'guerrillamail.info', 'guerrillamail.biz',
        'guerrillamail.de', 'guerrillamail.net', 'guerrillamail.org', 'sharklasers.com',
        'tempmail.com', 'temp-mail.org', 'temp-mail.io',
        '10minutemail.com', '10minutemail.net',
        'yopmail.com', 'yopmail.fr', 'yopmail.net',
        'trashmail.com', 'trashmail.net', 'throwawaymail.com',
        'getnada.com', 'mailnesia.com', 'maildrop.cc', 'mintemail.com',
        'fakeinbox.com', 'dispostable.com', 'emailondeck.com',
        'mohmal.com', 'moakt.cc', 'moakt.com',
        'discardmail.com', 'spamgourmet.com', 'mailcatch.com',
        'tempinbox.com', 'mail-temp.com', 'anonbox.net', 'mytemp.email',
        'incognitomail.com', 'spam4.me', 'burnermail.io',
        'crazymailing.com', 'mailsac.com', 'inboxbear.com', 'tempr.email',
    ];

    /**
     * Stricter format check than relying on Laravel's `email` rule alone.
     * Catches obvious junk before any DNS or API work.
     */
    public function isValidFormat(string $email): bool
    {
        $email = trim($email);

        if ($email === '' || strlen($email) > 254) {
            return false;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $atPosition = strrpos($email, '@');

        if ($atPosition === false || $atPosition === 0 || $atPosition === strlen($email) - 1) {
            return false;
        }

        $local = substr($email, 0, $atPosition);
        $domain = strtolower(substr($email, $atPosition + 1));

        if ($local === '' || $domain === '' || ! str_contains($domain, '.')) {
            return false;
        }

        if (str_contains($email, '..')) {
            return false;
        }

        if (str_starts_with($local, '.') || str_ends_with($local, '.')) {
            return false;
        }

        if (str_starts_with($domain, '.') || str_ends_with($domain, '.') || str_ends_with($domain, '-')) {
            return false;
        }

        $tld = substr($domain, strrpos($domain, '.') + 1);

        if ($tld === '' || strlen($tld) < 2 || ! preg_match('/^[a-z0-9-]+$/i', $tld)) {
            return false;
        }

        return true;
    }

    /**
     * Whether the email's domain is a known disposable/throwaway provider.
     */
    public function isDisposableEmailDomain(string $email): bool
    {
        $domain = $this->extractEmailDomain($email);

        if ($domain === null) {
            return false;
        }

        $domains = Hook::applyFilters(
            CommonFilterHook::REGISTRATION_DISPOSABLE_EMAIL_DOMAINS,
            self::DISPOSABLE_EMAIL_DOMAINS
        );

        $domains = array_map(static fn ($d) => strtolower((string) $d), $domains);

        return in_array($domain, $domains, true);
    }

    /**
     * Whether the email's domain resolves to a mail server (MX) or at least a host (A/AAAA).
     * Real DNS lookups are skipped during automated tests to avoid network dependency/flakiness.
     */
    public function domainAcceptsMail(string $email): bool
    {
        $domain = $this->extractEmailDomain($email);

        if ($domain === null) {
            return false;
        }

        if (app()->runningUnitTests()) {
            return true;
        }

        return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A') || checkdnsrr($domain, 'AAAA');
    }

    /**
     * Convenience combined check: valid format, not disposable, domain accepts mail.
     */
    public function isPlausiblyDeliverable(string $email): bool
    {
        if (! $this->isValidFormat($email)) {
            return false;
        }

        if ($this->isDisposableEmailDomain($email)) {
            return false;
        }

        return $this->domainAcceptsMail($email);
    }

    private function extractEmailDomain(string $email): ?string
    {
        $atPosition = strrpos($email, '@');

        if ($atPosition === false || $atPosition === strlen($email) - 1) {
            return null;
        }

        $domain = trim(substr($email, $atPosition + 1));

        return $domain === '' ? null : strtolower($domain);
    }
}
