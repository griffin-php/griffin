<?php

declare(strict_types=1);

namespace GriffinTest\Planner;

use Griffin\Migration\Container;
use Griffin\Migration\MigrationInterface;
use Griffin\Planner\Exception;
use Griffin\Planner\Planner;
use PHPUnit\Framework\TestCase;

class PlannerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->planner   = new Planner($this->container);
    }

    /**
     * @param string[] $dependencies
     */
    protected function createMigration(string $name, array $dependencies = []): MigrationInterface
    {
        $migration = $this->createMock(MigrationInterface::class);

        $migration->method('getName')
            ->will($this->returnValue($name));

        $migration->method('getDependencies')
            ->will($this->returnValue($dependencies));

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

    public function testUpDependencies(): void
    {
        $container = $this->planner->getContainer();

        $migrationA = $this->createMigration('A', ['B']);
        $migrationB = $this->createMigration('B');

        $container
            ->addMigration($migrationA)
            ->addMigration($migrationB);

        $this->assertSame([$migrationB, $migrationA], $this->planner->up()->getMigrations());
    }

    public function testUpDependenciesDeep(): void
    {
        $container = $this->planner->getContainer();

        $migrations = [
            $this->createMigration('A'),
            $this->createMigration('B', ['A', 'C']),
            $this->createMigration('C', ['D']),
            $this->createMigration('D'),
        ];

        foreach ($migrations as $migration) {
            $container->addMigration($migration);
        }

        $this->assertSame(['A', 'D', 'C', 'B'], $this->planner->up()->getMigrationNames());
    }

    public function testUpDependenciesCircular(): void
    {
        $this->expectException(Exception::class);

        $container = $this->planner->getContainer();

        $container
            ->addMigration($this->createMigration('A', ['B']))
            ->addMigration($this->createMigration('B', ['C']))
            ->addMigration($this->createMigration('C', ['A']));

        $this->planner->up();
    }

    public function testUpDependenciesSelf(): void
    {
        $this->expectException(Exception::class);

        $this->planner->getContainer()
            ->addMigration($this->createMigration('A', ['A']));

        $this->planner->up();
    }
}
