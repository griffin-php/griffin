<?php

declare(strict_types=1);

namespace GriffinTest\Runner\Runner;

use Griffin\Runner\Runner;
use GriffinTest\Runner\MigrationTrait;
use PHPUnit\Framework\TestCase;

class UpNamedTest extends TestCase
{
    use MigrationTrait;

    protected function setUp(): void
    {
        $this->runner = new Runner();
    }

    public function testMigration(): void
    {
        $container = $this->createContainer();

        $migrationA = $this->createMigration('A');
        $migrationB = $this->createMigration('B');

        // Migration A Only
        $this->assertMigrationAssert($container, $migrationA);
        $this->assertMigrationUp($container, $migrationA);

        $this->runner->addMigration($migrationA);
        $this->runner->addMigration($migrationB);

        $this->runner->up('A');

        $this->assertContains('A', $container->up);
        $this->assertNotContains('B', $container->up);
    }

    public function testMigrationWithDependencies(): void
    {
        $container = $this->createContainer();

        $graph1 = [
            $this->createMigration('A'),
            $this->createMigration('B'),
            $this->createMigration('C', ['A', 'B']),
        ];

        $graph2 = [
            $this->createMigration('D'),
            $this->createMigration('E'),
            $this->createMigration('F', ['D', 'E']),
        ];

        foreach ($graph1 as $migration) {
            $this->assertMigrationAssert($container, $migration);
            $this->assertMigrationUp($container, $migration);
            $this->runner->addMigration($migration);
        }

        foreach ($graph2 as $migration) {
            $this->runner->addMigration($migration);
        }

        $this->runner->up('C');

        $this->assertContains('A', $container->up);
        $this->assertContains('B', $container->up);
        $this->assertContains('C', $container->up);

        $this->assertNotContains('D', $container->up);
        $this->assertNotContains('E', $container->up);
        $this->assertNotContains('F', $container->up);
    }

    public function testMigrationMultiple(): void
    {
        $container = $this->createContainer();

        $migrations = [
            $this->createMigration('A'),
            $this->createMigration('B'),
        ];

        foreach ($migrations as $migration) {
            $this->assertMigrationAssert($container, $migration);
            $this->assertMigrationUp($container, $migration);
            $this->runner->addMigration($migration);
        }

        $this->runner->addMigration($this->createMigration('C'));

        $this->runner->up('A', 'B');

        $this->assertContains('A', $container->up);
        $this->assertContains('B', $container->up);

        $this->assertNotContains('C', $container->up);
    }
}
