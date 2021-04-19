<?php

declare(strict_types=1);

namespace GriffinTest\Migration\Migration;

use Griffin\Migration\Exception;
use GriffinTest\Migration\OperatorInterface;
use GriffinTest\Migration\SetUpTrait;
use PHPUnit\Framework\TestCase;

class UpTest extends TestCase
{
    use SetUpTrait;

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
