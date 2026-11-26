<?php

declare(strict_types=1);

namespace Tests\Unit;

use Admin\SitePath;
use Codeception\Test\Unit;

class SitePathTest extends Unit
{
    public function testAPathOnThisSiteIsOne(): void
    {
        foreach (['/', '/profile/', '/admin/migrate_tables.php', '/a?b=1&c=2'] as $path) {
            $this->assertTrue(SitePath::is($path), $path);
        }
    }

    public function testAnythingElseIsNot(): void
    {
        $not_paths = [
            '',
            'https://evil.example/',
            '//evil.example',
            '/\\evil.example',
            'javascript:alert(1)',
            'profile/',
            "/a\nb",
            '/' . str_repeat('a', 255),
        ];
        foreach ($not_paths as $bad) {
            $this->assertFalse(SitePath::is($bad), $bad);
        }
    }

    public function testTheLongestAllowedPathIsOne(): void
    {
        $this->assertTrue(SitePath::is('/' . str_repeat('a', 254)));
    }
}
