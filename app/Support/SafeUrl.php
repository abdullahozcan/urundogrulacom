<?php

namespace App\Support;

use Illuminate\Support\Facades\Filter;

/**
 * Outbound URL validation for admin-supplied URLs (webhooks, redirects).
 *
 * `filter_var($url, FILTER_VALIDATE_URL)` only checks syntax. It happily
 * accepts `http://169.254.169.254/latest/meta-data/`, `http://127.0.0.1:6379/`
 * and `http://2130706433/`, which turns any company admin with rule-create
 * rights into an SSRF primitive against the cloud metadata service or the
 * internal network. `https` is required and the resolved host must be public.
 */
final class SafeUrl
{
    /** Hosts that always resolve back to the machine itself. */
    private const BLOCKED_HOSTS = [
        'localhost',
        'localhost.localdomain',
        'ip6-localhost',
        'ip6-loopback',
        'metadata.google.internal',
    ];

    public static function isSafe(?string $url): bool
    {
        return self::violation($url) === null;
    }

    /**
     * Returns a human readable reason when the URL is not acceptable.
     */
    public static function violation(?string $url): ?string
    {
        if (blank($url)) {
            return 'URL boş olamaz.';
        }

        if (mb_strlen($url) > 2000) {
            return 'URL çok uzun.';
        }

        $parts = parse_url($url);

        if ($parts === false || empty($parts['host']) || empty($parts['scheme'])) {
            return 'Geçersiz URL.';
        }

        $scheme = strtolower($parts['scheme']);

        // `javascript:`, `data:`, `file:`, `gopher:` all fail here.
        if ($scheme !== 'https' && $scheme !== 'http') {
            return 'Yalnızca http ve https şemalarına izin verilir.';
        }

        if (self::hostIsPrivate($parts['host'])) {
            return 'Yerel veya dahili ağ adreslerine izin verilmez.';
        }

        return null;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function assert(?string $url, string $context = 'URL'): string
    {
        $violation = self::violation($url);

        if ($violation !== null) {
            throw new \InvalidArgumentException($context . ': ' . $violation);
        }

        return $url;
    }

    private static function hostIsPrivate(string $host): bool
    {
        $host = strtolower(trim($host, '[]'));

        if ($host === '' || in_array($host, self::BLOCKED_HOSTS, true)) {
            return true;
        }

        // Any .local / .internal / .lan style suffix targets the local network.
        foreach (['.local', '.localhost', '.internal', '.lan', '.home.arpa'] as $suffix) {
            if (str_ends_with($host, $suffix)) {
                return true;
            }
        }

        // Normalise the non-standard integer / hex / octal forms of an IPv4
        // address. `http://2130706433/` and `http://0x7f000001/` are both
        // 127.0.0.1 and would otherwise sail past a plain IP check.
        $literal = self::normaliseIpLiteral($host);

        if ($literal !== null) {
            return ! filter_var($literal, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }

        // Hostnames are resolved so that a public name pointing at 127.0.0.1
        // (DNS rebinding) is rejected too. Resolution failure is treated as
        // unsafe rather than allowed through.
        $addresses = filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
            ? (gethostbynamel($host) ?: [$host])
            : [$host];

        foreach ($addresses as $address) {
            $normalised = self::normaliseIpLiteral(strtolower($address)) ?? $address;

            if (filter_var($normalised, FILTER_VALIDATE_IP)) {
                if (! filter_var($normalised, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return true;
                }
            } elseif (in_array(strtolower($address), self::BLOCKED_HOSTS, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the dotted-quad form of a host if it is an IP literal in any
     * accepted encoding, otherwise null.
     */
    private static function normaliseIpLiteral(string $host): ?string
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $host;
        }

        // Bare decimal: 2130706433 => 127.0.0.1
        if (ctype_digit($host)) {
            $long = (int) $host;

            return ($long >= 0 && $long <= 4294967295) ? long2ip($long) : null;
        }

        // Bare hex: 0x7f000001 => 127.0.0.1
        if (preg_match('/^0x[0-9a-f]{1,8}$/', $host)) {
            return long2ip((int) hexdec($host));
        }

        // Dotted parts that may be decimal, octal (0-prefixed) or hex.
        $parts = explode('.', $host);

        if (count($parts) === 4) {
            $values = [];

            foreach ($parts as $part) {
                if (preg_match('/^0x[0-9a-f]{1,2}$/', $part)) {
                    $values[] = (int) hexdec(substr($part, 2));
                } elseif (preg_match('/^0[0-7]{1,3}$/', $part)) {
                    $values[] = (int) octdec(substr($part, 1));
                } elseif (preg_match('/^\d{1,3}$/', $part)) {
                    $values[] = (int) $part;
                } else {
                    return null;
                }
            }

            foreach ($values as $value) {
                if ($value < 0 || $value > 255) {
                    return null;
                }
            }

            return implode('.', $values);
        }

        // Short forms such as 127.1 or 127.0.1 are valid IPv4 notations that
        // expand to loopback.
        if (count($parts) >= 2 && count($parts) <= 3 && ctype_digit(implode('', $parts))) {
            $long = 0;

            foreach ($parts as $index => $part) {
                $long += ((int) $part) << (8 * (3 - $index));
            }

            return long2ip($long);
        }

        return null;
    }
}