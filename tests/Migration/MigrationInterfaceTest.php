<?php

declare(strict_types=1);

namespace GriffinTest\Migration;

use Griffin\Migration\MigrationInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class MigrationInterfaceTest extends TestCase
{
    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(MigrationInterface::class);
    }

    public function testInterface(): void
    {
        $this->assertTrue($this->reflection->isInterface());
    }

    public function testName(): void
    {
        $method = $this->reflection->getMethod('getName');

        $this->assertTrue($method->isPublic());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('string', (string) $method->getReturnType());
    }

    public function testAssert(): void
    {
        $method = $this->reflection->getMethod('assert');

        $this->assertTrue($method->isPublic());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('bool', (string) $method->getReturnType());
    }

    public function testUp(): void
    {
        $method = $this->reflection->getMethod('up');

        $this->assertTrue($method->isPublic());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('void', (string) $method->getReturnType());
    }

    public function testDown(): void
    {
        $method = $this->reflection->getMethod('down');

        $this->assertTrue($method->isPublic());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('void', (string) $method->getReturnType());
    }

    public function testDepends(): void
    {
        $method = $this->reflection->getMethod('depends');

        $this->assertTrue($method->isPublic());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('array', (string) $method->getReturnType());
    }
}
