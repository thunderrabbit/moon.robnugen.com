<?php

declare(strict_types=1);

namespace Tests\Unit;

use Codeception\Test\Unit;

class HeadersTest extends Unit
{
    public function testAllFiveHeadersAreThere(): void
    {
        $this->assertSame(
            [
                'X-Content-Type-Options',
                'Content-Security-Policy',
                'X-Frame-Options',
                'Referrer-Policy',
                'Strict-Transport-Security',
            ],
            array_keys(\Security\Headers::all()),
        );
    }

    public function testNoPageCanBeFramed(): void
    {
        $headers = \Security\Headers::all();

        $this->assertStringContainsString("frame-ancestors 'none'", $headers['Content-Security-Policy']);
        $this->assertSame('DENY', $headers['X-Frame-Options']);
    }

    public function testThePolicyPromisesNoScriptSource(): void
    {
        $this->assertStringNotContainsString('script-src', \Security\Headers::all()['Content-Security-Policy']);
    }

    public function testHstsLeavesSubdomainsAlone(): void
    {
        $hsts = \Security\Headers::all()['Strict-Transport-Security'];

        $this->assertSame('max-age=31536000', $hsts);
        $this->assertStringNotContainsString('includeSubDomains', $hsts);
    }
}
