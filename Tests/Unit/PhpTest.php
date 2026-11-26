<?php

declare(strict_types=1);

namespace Tests\Unit;

use Codeception\Test\Unit;

class PhpTest extends Unit
{
    public function testTheFloorIsPhp86(): void
    {
        $this->assertSame(80600, \Version\Php::FLOOR_ID);
        $this->assertSame('8.6', \Version\Php::FLOOR);
    }

    public function testJustBelowTheFloorIsRefused(): void
    {
        $this->assertFalse(\Version\Php::meetsFloor(80599));
        $this->assertFalse(\Version\Php::meetsFloor(80500));
    }

    public function testTheFloorAndAboveAreAccepted(): void
    {
        $this->assertTrue(\Version\Php::meetsFloor(80600));
        $this->assertTrue(\Version\Php::meetsFloor(80601));
        $this->assertTrue(\Version\Php::meetsFloor(90000));
    }
}
