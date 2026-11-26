<?php

declare(strict_types=1);

namespace Admin;

/**
 * Whether a string is a path on this site, the one rule a notification's link
 * is stored and drawn by (mg #851). It starts with one slash, never two, and
 * has no backslash, scheme or anything else a browser could read as another
 * host, so a link can never send an admin elsewhere.
 */
final class SitePath
{
    /** The link column's width. */
    public const int MAX = 255;

    public static function is(string $link): bool
    {
        return strlen($link) <= self::MAX
            && preg_match('#\A/(?![/\\\\])[A-Za-z0-9/._~%?=&+-]*\z#', $link) === 1;
    }
}
