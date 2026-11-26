<?php

declare(strict_types=1);

namespace Version;

/**
 * The oldest PHP this site runs on.
 *
 * PHP 8.6 made sessions strict, HttpOnly and SameSite=Lax by default, and the
 * site was reviewed against that behaviour. On an older PHP (a domain switched
 * back in the panel, a new server, a restored backup) it stops instead of
 * serving pages nobody checked there.
 */
final class Php
{
    public const int FLOOR_ID = 80600;
    public const string FLOOR = '8.6';

    public static function meetsFloor(int $version_id = PHP_VERSION_ID): bool
    {
        return $version_id >= self::FLOOR_ID;
    }

    /**
     * Answer 503 and stop when the running PHP is below the floor.
     *
     * Call it first, straight after the autoloader. The page names only the
     * floor; the running version goes to the log.
     */
    public static function requireFloor(): void
    {
        if (self::meetsFloor()) {
            return;
        }

        error_log('PHP ' . PHP_VERSION . ' is below the floor of ' . self::FLOOR);
        http_response_code(503);
        echo '<h1>Site unavailable</h1><p>This site needs PHP ' . self::FLOOR . ' or later.</p>';
        exit;
    }
}
