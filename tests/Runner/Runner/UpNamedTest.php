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

    public function testUpWithMigrationDefined(): void
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
}
