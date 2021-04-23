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
        $this->assertMigrationAssert($container, $migrationB);

        // Migration A Only
        $this->assertMigrationDown($container, $migrationA);
        $this->assertNotMigrationDown($container, $migrationB);

        $this->runner->addMigration($migrationA);
        $this->runner->addMigration($migrationB);

        $this->runner->down('A');

        $this->assertContains('A', $container->down);
        $this->assertNotContains('B', $container->down);
    }
}
