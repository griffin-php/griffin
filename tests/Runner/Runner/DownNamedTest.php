<?php

declare(strict_types=1);

namespace GriffinTest\Runner\Runner;

use Griffin\Runner\Runner;
use GriffinTest\Runner\MigrationTrait;
use PHPUnit\Framework\TestCase;

class DownNamedTest extends TestCase
{
    use MigrationTrait;

    protected function setUp(): void
    {
        $this->runner = new Runner();
    }

    public function testMigration(): void
    {
        $container = $this->createContainer(['A', 'B']);

        $migrationA = $this->createMigration('A');
        $migrationB = $this->createMigration('B');

        $this->assertMigrationAssert($container, $migrationA);
        $this->assertNotMigrationAssert($container, $migrationB);

        // Migration A Only
        $this->assertMigrationDown($container, $migrationA);
        $this->assertNotMigrationDown($container, $migrationB);

        $this->runner->addMigration($migrationA);
        $this->runner->addMigration($migrationB);

        $this->runner->down('A');

        $this->assertContains('A', $container->down);
        $this->assertNotContains('B', $container->down);
    }

    public function testMigrationWithDependencies(): void
    {
        $container = $this->createContainer(['A', 'B', 'C']);

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
            $this->assertMigrationDown($container, $migration);
            $this->runner->addMigration($migration);
        }

        foreach ($graph2 as $migration) {
            $this->assertNotMigrationAssert($container, $migration);
            $this->assertNotMigrationDown($container, $migration);
            $this->runner->addMigration($migration);
        }

        $this->runner->down('A');
        $this->runner->down('B');

        $this->assertContains('A', $container->down);
        $this->assertContains('B', $container->down);
        $this->assertContains('C', $container->down);

        $this->assertNotContains('D', $container->down);
        $this->assertNotContains('E', $container->down);
        $this->assertNotContains('F', $container->down);
    }
}
