<?php

declare(strict_types=1);

namespace GriffinTest\Migration;

use Griffin\Migration\AbstractMigration;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AbstractMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(AbstractMigration::class);
    }

    public function testAssert(): void
    {
        $method = $this->reflection->getMethod('assert');

        $this->assertTrue($method->isAbstract());
        $this->assertTrue($method->isPublic());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('bool', (string) $method->getReturnType());
    }

    public function testUp(): void
    {
        $method = $this->reflection->getMethod('up');

        $this->assertTrue($method->isAbstract());
        $this->assertTrue($method->isPublic());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('void', (string) $method->getReturnType());
    }

    public function testDown(): void
    {
        $method = $this->reflection->getMethod('down');

        $this->assertTrue($method->isAbstract());
        $this->assertTrue($method->isPublic());
        $this->assertEquals(0, $method->getNumberOfParameters());
        $this->assertEquals('void', (string) $method->getReturnType());
    }
}
