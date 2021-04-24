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

    public function testUp(): void
    {
        $migration = $this->migration->withUp(fn() => null);

        $this->assertNotSame($this->migration, $migration); // Immutability
    }

    public function testCallable(): void
    {
        $operator = $this->createMock(OperatorInterface::class);

        $operator->expects($this->once())
            ->method('operate');

        $this->migration->withUp([$operator, 'operate'])->up();
    }

    public function testInvokable(): void
    {
        $operator = $this->createMock(OperatorInterface::class);

        $operator->expects($this->once())
            ->method('__invoke');

        $this->migration->withUp($operator)->up();
    }

    public function testWithoutUp(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::CALLABLE_UNKNOWN);
        $this->expectExceptionMessage('Unknown Callable: "up"');

        $this->migration->up();
    }
}
