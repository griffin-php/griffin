<?php

declare(strict_types=1);

namespace GriffinTest\Migration;

use Griffin\Migration\Exception;
use Griffin\Migration\Migration;
use PHPUnit\Framework\TestCase;

class MigrationUpTest extends TestCase
{
    protected function setUp(): void
    {
        $this->migration = new Migration();
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
