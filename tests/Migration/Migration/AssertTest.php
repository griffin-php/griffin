<?php

declare(strict_types=1);

namespace GriffinTest\Migration\Migration;

use Griffin\Migration\Exception;
use GriffinTest\Migration\OperatorInterface;
use GriffinTest\Migration\SetUpTrait;
use PHPUnit\Framework\TestCase;

class AssertTest extends TestCase
{
    use SetUpTrait;

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
}
