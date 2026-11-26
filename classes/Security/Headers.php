<?php

declare(strict_types=1);

namespace Security;

/**
 * The response headers every page sends, from one place so no page has to
 * remember them.
 *
 * There is no script-src: the templates carry inline scripts, and a policy
 * that allowed those would promise more than it delivers. HSTS has no
 * includeSubDomains: a site must not decide that for hosts it does not run.
 */
final class Headers
{
    /**
     * @return array<string, string> header name => value
     */
    public static function all(): array
    {
        return [
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "frame-ancestors 'none'; base-uri 'self'",
            // For browsers older than frame-ancestors.
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'same-origin',
            'Strict-Transport-Security' => 'max-age=31536000',
        ];
    }

    public static function send(): void
    {
        foreach (self::all() as $name => $value) {
            header($name . ': ' . $value);
        }
    }
}
