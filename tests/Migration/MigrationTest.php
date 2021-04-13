<?php

declare(strict_types=1);

namespace GriffinTest\Migration;

use Closure;
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

    public function testWithAssert(): void
    {
        $noop = fn() => null;

        $migration = $this->migration->withAssert($noop);

        $this->assertNotSame($this->migration, $migration);
        $this->assertNull($this->migration->getAssert());
        $this->assertSame($noop, $migration->getAssert());
    }

    public function testWithAssertCallable(): void
    {
        $object = new class{
            public function noop(): void {}
        };

        $migration = $this->migration->withAssert([$object, 'noop']);

        $this->assertInstanceOf(Closure::class, $migration->getAssert());
    }

    public function testWithUp(): void
    {
        $noop = fn() => null;

        $migration = $this->migration->withUp($noop);

        $this->assertNotSame($this->migration, $migration);
        $this->assertNull($this->migration->getUp());
        $this->assertSame($noop, $migration->getUp());
    }
}
