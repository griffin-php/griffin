<?php

declare(strict_types=1);

namespace GriffinTest\Runner\Runner;

use Griffin\Runner\Runner;
use GriffinTest\Runner\MigrationTrait;
use PHPUnit\Framework\TestCase;

class DownTest extends TestCase
{
    use MigrationTrait;

    protected function setUp(): void
    {
        $this->runner = new Runner();
    }

    public function testDown(): void
    {
        $this->assertSame($this->runner, $this->runner->down());
    }

    public function testDownWithMigration(): void
    {
        $container = $this->createContainer(['A']);

        $migration = $this->createMigration('A');

        $this->assertMigrationAssert($container, $migration);
        $this->assertMigrationDown($container, $migration);

        $this->runner->addMigration($migration)->down();
    }

    public function testDownWithMigrationWithDependency(): void
    {
        $container = $this->createContainer(['A', 'B']);

        $migrations = [
            $this->createMigration('B'),
            $this->createMigration('A', ['B']),
        ];

        foreach ($migrations as $migration) {
            $this->assertMigrationAssert($container, $migration);
            $this->assertMigrationDown($container, $migration);
            $this->runner->addMigration($migration);
        }

        $this->runner->down();

        $this->assertSame([], $container->status);
        $this->assertSame(['A', 'B'], $container->down); // Must Respect Order
    }
}
