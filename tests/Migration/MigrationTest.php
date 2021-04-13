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

    public function testName(): void
    {
        $migration = $this->migration->withName('foobar');

        $this->assertNotSame('foobar', $this->migration->getName());
        $this->assertNotSame($this->migration, $migration); // Immutability
        $this->assertSame('foobar', $migration->getName());
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
        $migration = $this->migration->withAssert([new Operator(), 'noop']);

        $this->assertInstanceOf(Closure::class, $migration->getAssert());
    }

    public function testWithAssertInvokable(): void
    {
        $migration = $this->migration->withAssert(new Operator());

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

    public function testWithUpCallable(): void
    {
        $migration = $this->migration->withUp([new Operator(), 'noop']);

        $this->assertInstanceOf(Closure::class, $migration->getUp());
    }

    public function testWithUpInvokable(): void
    {
        $migration = $this->migration->withUp(new Operator());

        $this->assertInstanceOf(Closure::class, $migration->getUp());
    }
}
