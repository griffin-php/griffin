<?php

declare(strict_types=1);

namespace GriffinTest\Migration;

use Griffin\Migration\Migration;
use Griffin\Migration\MigrationInterface;
use PHPUnit\Framework\TestCase;

class MigrationTest extends TestCase
{
    use SetUpTrait;

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
}
