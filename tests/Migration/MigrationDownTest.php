<?php

declare(strict_types=1);

namespace GriffinTest\Migration;

use Griffin\Migration\Exception;
use Griffin\Migration\Migration;
use GriffinTest\Migration\OperatorInterface;
use PHPUnit\Framework\TestCase;

class MigrationDownTest extends TestCase
{
    protected function setUp(): void
    {
        $this->migration = new Migration();
    }

    public function testWithDown(): void
    {
        $migration = $this->migration->withDown(fn() => null);

        $this->assertNotSame($this->migration, $migration); // Immutability
    }

    public function testWithDownCallable(): void
    {
        $operator = $this->createMock(OperatorInterface::class);

        $operator->expects($this->once())
            ->method('operate');

        $this->migration->withDown([$operator, 'operate'])->down();
    }

    public function testWithDownInvokable(): void
    {
        $operator = $this->createMock(OperatorInterface::class);

        $operator->expects($this->once())
            ->method('__invoke');

        $this->migration->withDown($operator)->down();
    }

    public function testWithoutDown(): void
    {
        $this->expectException(Exception::class);

        $this->migration->down();
    }
}
