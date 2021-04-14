<?php

declare(strict_types=1);

namespace GriffinTest\Migration;

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
        $migration = $this->migration->withAssert(fn() => null);

        $this->assertNotSame($this->migration, $migration); // Immutability
    }

    public function testWithAssertCallable(): void
    {
        $operator = $this->createMock(OperatorInterface::class);

        $operator->expects($this->once())
            ->method('operate')
            ->will($this->returnValue(true));

        $migration = $this->migration->withAssert([$operator, 'operate']);

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
        $migration = $this->migration->withUp(fn() => null);

        $this->assertNotSame($this->migration, $migration); // Immutability
    }

    public function testWithUpCallable(): void
    {
        $operator = $this->createMock(OperatorInterface::class);

        $operator->expects($this->once())
            ->method('operate');

        $this->migration->withUp([$operator, 'operate'])->up();
    }

    public function testWithUpInvokable(): void
    {
        $operator = $this->createMock(OperatorInterface::class);

        $operator->expects($this->once())
            ->method('__invoke');

        $this->migration->withUp($operator)->up();
    }

    public function testWithoutUp(): void
    {
        $this->expectException(Exception::class);

        $this->migration->up();
    }
}
