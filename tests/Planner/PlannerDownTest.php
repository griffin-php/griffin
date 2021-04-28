<?php

declare(strict_types=1);

namespace GriffinTest\Planner;

use Griffin\Migration\Exception as MigrationException;
use Griffin\Planner\Exception;
use PHPUnit\Framework\TestCase;

class PlannerDownTest extends TestCase
{
    use PlannerTrait;

    public function testBasic(): void
    {
        $container = $this->planner->getContainer();

        $migrationA = $this->createMigration('A');
        $migrationB = $this->createMigration('B');

        $container->addMigration($migrationA);

        $migrations = $this->planner->down();

        $this->assertCount(1, $migrations);
        $this->assertContains($migrationA, $migrations);

        $container->addMigration($migrationB);

        $migrations = $this->planner->down();

        $this->assertCount(2, $migrations);
        $this->assertContains($migrationB, $migrations);
    }

    public function testDependencies(): void
    {
        $container = $this->planner->getContainer();

        $migrationA = $this->createMigration('A');
        $migrationB = $this->createMigration('B', ['A']);

        $container
            ->addMigration($migrationA)
            ->addMigration($migrationB);

        $this->assertSame(['B', 'A'], $this->planner->down()->getMigrationNames());
    }

    public function testDependenciesInvalid(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::DEPENDENCY_INVALID);
        $this->expectExceptionMessage('Invalid Migration "A" Dependency Data Type: "double"');

        $container = $this->planner->getContainer();

        $container->addMigration($this->createMigration('A', [3.1415]));

        $this->planner->down();
    }

    public function testDependenciesDeep(): void
    {
        $container = $this->planner->getContainer();

        $migrations = [
            $this->createMigration('A'),
            $this->createMigration('B', ['D']),
            $this->createMigration('C', ['B']),
            $this->createMigration('D', ['A']),
        ];

        foreach ($migrations as $migration) {
            $container->addMigration($migration);
        }

        $this->assertSame(['C', 'B', 'D', 'A'], $this->planner->down()->getMigrationNames());
    }

    public function testDependenciesCircular(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::DEPENDENCY_CIRCULAR);
        $this->expectExceptionMessage('Circular Dependency Found: "A, C, B, A"');

        $container = $this->planner->getContainer();

        $container
            ->addMigration($this->createMigration('A', ['B']))
            ->addMigration($this->createMigration('B', ['C']))
            ->addMigration($this->createMigration('C', ['A']));

        $this->planner->down();
    }

    public function testDependenciesSelf(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::DEPENDENCY_CIRCULAR);
        $this->expectExceptionMessage('Circular Dependency Found: "A, A"');

        $this->planner->getContainer()
            ->addMigration($this->createMigration('A', ['A']));

        $this->planner->down();
    }

    public function testDependencyUnknown(): void
    {
        $this->expectException(MigrationException::class);
        $this->expectExceptionCode(MigrationException::NAME_UNKNOWN);
        $this->expectExceptionMessage('Unknown Migration Name: "B"');

        $this->planner->getContainer()
            ->addMigration($this->createMigration('A', ['B']));

        $this->planner->down();
    }

    public function testNamed(): void
    {
        $container = $this->planner->getContainer();

        $container
            ->addMigration($this->createMigration('A'))
            ->addMigration($this->createMigration('B'))
            ->addMigration($this->createMigration('C', ['D']))
            ->addMigration($this->createMigration('D'));

        $migrations = $this->planner->down('A', 'D')->getMigrationNames();

        $this->assertCount(3, $migrations);
        $this->assertContains('A', $migrations);
        $this->assertNotContains('B', $migrations);
        $this->assertContains('C', $migrations);
        $this->assertContains('D', $migrations);
    }
}
