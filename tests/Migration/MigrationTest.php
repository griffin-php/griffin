<?php

declare(strict_types=1);

namespace GriffinTest\Migration;

use Closure;
use Griffin\Migration\Exception;
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

        $this->assertSame(Migration::class, $this->migration->getName());
        $this->assertSame('foobar', $migration->getName());
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
        $operator = $this->createMock(OperatorInterface::class);

        $operator->expects($this->once())
            ->method('assert')
            ->will($this->returnValue(true));

        $migration = $this->migration->withAssert([$operator, 'assert']);

        $this->assertTrue($migration->assert());
    }

    public function testWithAssertInvokable(): void
    {
        $operator = $this->createMock(OperatorInterface::class);

        $operator->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue(true));

        $migration = $this->migration->withAssert($operator);

        $this->assertTrue($migration->assert());
    }

    public function testWithoutAssert(): void
    {
        $this->expectException(Exception::class);

        $this->migration->assert();
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
