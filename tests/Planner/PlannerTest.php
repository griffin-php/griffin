<?php

declare(strict_types=1);

namespace GriffinTest\Planner;

use Griffin\Migration\Container;
use Griffin\Migration\MigrationInterface;
use Griffin\Planner\Planner;
use PHPUnit\Framework\TestCase;

class PlannerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->planner   = new Planner($this->container);
    }

    protected function createMigration(string $name): MigrationInterface
    {
        $migration = $this->createMock(MigrationInterface::class);

        $migration->method('getName')
            ->will($this->returnValue($name));

        return $migration;
    }

    public function testContainer(): void
    {
        $this->assertSame($this->container, $this->planner->getContainer());
    }

    public function testUpBasic(): void
    {
        $container = $this->planner->getContainer();

        $migrationA = $this->createMigration('A');
        $migrationB = $this->createMigration('B');

        $container->addMigration($migrationA);

        $migrations = $this->planner->up();

        $this->assertCount(1, $migrations);
        $this->assertContains($migrationA, $migrations);

        $container->addMigration($migrationB);

        $migrations = $this->planner->up();

        $this->assertCount(2, $migrations);
        $this->assertContains($migrationB, $migrations);
    }
}
