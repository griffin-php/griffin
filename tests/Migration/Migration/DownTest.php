<?php

declare(strict_types=1);

namespace GriffinTest\Migration\Migration;

use Griffin\Migration\Exception;
use GriffinTest\Migration\OperatorInterface;
use GriffinTest\Migration\SetUpTrait;
use PHPUnit\Framework\TestCase;

class DownTest extends TestCase
{
    use SetUpTrait;

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
