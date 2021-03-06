<?php

declare(strict_types=1);

namespace GriffinTest\Migration;

use Griffin\Migration\Exception;
use Griffin\Migration\Migration;
use PHPUnit\Framework\TestCase;

class MigrationDownTest extends TestCase
{
    protected function setUp(): void
    {
        $this->migration = new Migration();
    }

    public function testDown(): void
    {
        $migration = $this->migration->withDown(fn() => null);

        $this->assertNotSame($this->migration, $migration); // Immutability
    }

    public function testCallable(): void
    {
        $operator = $this->createMock(OperatorInterface::class);

        $operator->expects($this->once())
            ->method('operate');

        $this->migration->withDown([$operator, 'operate'])->down();
    }

    public function testInvokable(): void
    {
        $operator = $this->createMock(OperatorInterface::class);

        $operator->expects($this->once())
            ->method('__invoke');

        $this->migration->withDown($operator)->down();
    }

    public function testWithoutDown(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::CALLABLE_UNKNOWN);
        $this->expectExceptionMessage('Unknown Callable: "down"');

        $this->migration->down();
    }
}
