<?php

declare(strict_types=1);

namespace GriffinTest\Migration;

use Griffin\Migration\Migration;
use Griffin\Migration\MigrationInterface;
use PHPUnit\Framework\TestCase;

class MigrationTest extends TestCase
{
    public function setUp(): void
    {
        $this->migration = new Migration();
    }

    public function testInterface(): void
    {
        $this->assertInstanceOf(MigrationInterface::class, $this->migration);
    }

    public function testDefault(): void
    {
        $this->assertFalse($this->migration->assert());
        $this->assertNull($this->migration->up());
        $this->assertTrue($this->migration->assert());
        $this->assertNull($this->migration->down());
        $this->assertFalse($this->migration->assert());
    }
}
