<?php

namespace App\Services;

class SafeRemoteUrl
{
    public function isSafe(string $url): bool
    {
        $parts = parse_url($url);

        if (! is_array($parts)) {
            return false;
        }

        $host = $parts['host'] ?? null;
        $scheme = $parts['scheme'] ?? null;

        if (! is_string($host) || ! is_string($scheme)) {
            return false;
        }

        if (! in_array(strtolower($scheme), ['http', 'https'], true)) {
            return false;
        }

        if (isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) {
            return false;
        }

        if ($this->isLocalHostname($host)) {
            return false;
        }

        $resolvedAddresses = $this->resolveAddresses($host);

        if ($resolvedAddresses === []) {
            return false;
        }

        foreach ($resolvedAddresses as $address) {
            if ($this->isPrivateOrReservedAddress($address)) {
                return false;
            }
        }

        return true;
    }

    private function isLocalHostname(string $host): bool
    {
        $normalizedHost = strtolower(rtrim($host, '.'));

        if ($normalizedHost === 'localhost') {
            return true;
        }

        return str_ends_with($normalizedHost, '.local');
    }

    /**
     * @return list<string>
     */
    private function resolveAddresses(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return [$host];
        }

        $records = dns_get_record($host, DNS_A | DNS_AAAA);

        if ($records === false) {
            return [];
        }

        $addresses = [];

        foreach ($records as $record) {
            $address = $record['ip'] ?? $record['ipv6'] ?? null;

            if (is_string($address)) {
                $addresses[] = $address;
            }
        }

        return array_values(array_unique($addresses));
    }

    private function isPrivateOrReservedAddress(string $address): bool
    {
        return filter_var(
            $address,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
