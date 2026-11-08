<?php

declare(strict_types=1);

namespace App\Support\Security;

/**
 * Validates the Ollama base URL before the server sends requests to it.
 *
 * Ollama normally runs on the same machine or private network, so loopback and private
 * addresses are allowed by default (turn that off with OLLAMA_ALLOW_PRIVATE_NETWORK=false).
 * What is never allowed: cloud metadata and other link-local addresses, credentials,
 * and any path, query or fragment. The URL must be a bare base URL, so requests can only
 * ever reach "<host>/api/chat", not an arbitrary internal endpoint.
 */
class OllamaUrlValidator
{
    /**
     * @var list<string>
     */
    private const BLOCKED_HOSTNAMES = [
        'metadata',
        'metadata.google.internal',
        'metadata.goog',
        'instance-data',
        'instance-data.ec2.internal',
    ];

    /**
     * Always blocked, even when private networks are allowed.
     *
     * @var list<string>
     */
    private const BLOCKED_RANGES = [
        '0.0.0.0/8',          // "this" network
        '169.254.0.0/16',     // link-local, incl. AWS/GCP/Azure/OCI metadata
        '100.100.100.200/32', // Alibaba Cloud metadata
        '224.0.0.0/4',        // multicast
        '240.0.0.0/4',        // reserved, incl. broadcast
        '::/128',             // unspecified
        'fe80::/10',          // IPv6 link-local
        'fd00:ec2::254/128',  // AWS IPv6 metadata
        'ff00::/8',           // IPv6 multicast
    ];

    /**
     * @return string|null An error message, or null when the URL may be used.
     */
    public function validate(string $url): ?string
    {
        $url = trim($url);

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return __('The Ollama base URL is not a valid URL.');
        }

        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return __('The Ollama base URL must start with http:// or https://.');
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            return __('The Ollama base URL must not contain a username or password.');
        }

        if (isset($parts['query']) || isset($parts['fragment']) || ! in_array($parts['path'] ?? '', ['', '/'], true)) {
            return __('The Ollama base URL must be the server address only, such as http://localhost:11434, without a path.');
        }

        $host = strtolower(trim((string) ($parts['host'] ?? ''), '[]'));

        if ($host === '' || in_array(rtrim($host, '.'), self::BLOCKED_HOSTNAMES, true)) {
            return __('The Ollama base URL points to a host that is not allowed.');
        }

        $ips = $this->resolve($host);

        if ($ips === []) {
            return __('The Ollama host could not be resolved.');
        }

        foreach ($ips as $ip) {
            if ($this->isBlockedIp($ip)) {
                return __('The Ollama base URL points to a restricted address.');
            }
        }

        return null;
    }

    public function isValid(string $url): bool
    {
        return $this->validate($url) === null;
    }

    /**
     * @return list<string>
     */
    private function resolve(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }

        // gethostbynamel() honours /etc/hosts (localhost, Docker service names).
        $ips = gethostbynamel($host) ?: [];

        foreach (@dns_get_record($host, DNS_AAAA) ?: [] as $record) {
            if (isset($record['ipv6'])) {
                $ips[] = $record['ipv6'];
            }
        }

        return array_values(array_unique($ips));
    }

    private function isBlockedIp(string $ip): bool
    {
        // Check IPv4-mapped IPv6 addresses (::ffff:169.254.169.254) as IPv4.
        if (preg_match('/^::ffff:(\d{1,3}(?:\.\d{1,3}){3})$/i', $ip, $mapped)) {
            $ip = $mapped[1];
        }

        foreach (self::BLOCKED_RANGES as $range) {
            if ($this->inRange($ip, $range)) {
                return true;
            }
        }

        if (config('ai.ollama.allow_private_network', true)) {
            return false;
        }

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    private function inRange(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr);
        $ipBin = @inet_pton($ip);
        $subnetBin = @inet_pton($subnet);

        if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
            return false;
        }

        $bits = (int) $bits;
        $bytes = intdiv($bits, 8);

        if (strncmp($ipBin, $subnetBin, $bytes) !== 0) {
            return false;
        }

        $remainder = $bits % 8;

        if ($remainder === 0) {
            return true;
        }

        $mask = chr((0xFF << (8 - $remainder)) & 0xFF);

        return ($ipBin[$bytes] & $mask) === ($subnetBin[$bytes] & $mask);
    }
}
