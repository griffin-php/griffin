<?php

declare(strict_types=1);

namespace GriffinTest\Runner;

use Griffin\Migration\MigrationInterface;
use Griffin\Runner\Exception;
use Griffin\Runner\Runner;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use StdClass;

class RunnerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->runner = new Runner();
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

    protected function assertMigrationAssert(StdClass $container, MockObject $migration): void
    {
        $migration->expects($this->atLeast(1))
            ->method('assert')
            ->will($this->returnCallback(fn() => array_search($migration->getName(), $container->result) !== false));
    }

    protected function assertMigrationUp(StdClass $container, MockObject $migration): void
    {
        $migration->expects($this->once())
            ->method('up')
            ->will($this->returnCallback(fn() => array_push($container->result, $migration->getName())));
    }

    public function testMigrations(): void
    {
        $this->assertSame([], $this->runner->getMigrations());

        $migration = $this->createMigration('MIGRATION');

        $this->assertSame($this->runner, $this->runner->addMigration($migration));
        $this->assertSame([$migration], $this->runner->getMigrations());

        $otherMigration   = $this->createMigration('MIGRATION_OTHER');
        $anotherMigration = $this->createMigration('MIGRATION_ANOTHER');

        $this->runner
            ->addMigration($otherMigration)
            ->addMigration($anotherMigration);

        $this->assertSame([$migration, $otherMigration, $anotherMigration], $this->runner->getMigrations());
    }

    public function testMigrationsDuplicated(): void
    {
        $this->expectException(Exception::class);

        $migrationOne = $this->createMigration('MIGRATION');
        $migrationTwo = $this->createMigration('MIGRATION');

        $this->runner
            ->addMigration($migrationOne)
            ->addMigration($migrationTwo); // Duplicated
    }

    public function testUp(): void
    {
        $this->assertSame($this->runner, $this->runner->up());
    }

    public function testUpWithMigration(): void
    {
        // Container
        $container = new StdClass();
        // Container Logger
        $container->result = [];

        $migration = $this->createMigration('A');

        $this->assertMigrationAssert($container, $migration);
        $this->assertMigrationUp($container, $migration);

        $this->runner->addMigration($migration)->up();
    }

    public function testUpWithMigrationWithDependency(): void
    {
        // Container
        $container = new StdClass();
        // Container Logger
        $container->result = [];

        $migrations = [
            $this->createMigration('A', ['B']),
            $this->createMigration('B'),
        ];

        foreach ($migrations as $migration) {
            $this->assertMigrationAssert($container, $migration);
            $this->assertMigrationUp($container, $migration);
            $this->runner->addMigration($migration);
        }

        $this->runner->up();

        $this->assertSame(['B', 'A'], $container->result);
    }

    public function testUpWithMigrationWithMultipleDependencies(): void
    {
        // Container
        $container = new StdClass();
        // Container Logger
        $container->result = [];

        $migrations = [
            $this->createMigration('A', ['B', 'C', 'D']),
            $this->createMigration('B'),
            $this->createMigration('C'),
            $this->createMigration('D'),
        ];

        foreach ($migrations as $migration) {
            $this->assertMigrationAssert($container, $migration);
            $this->assertMigrationUp($container, $migration);
            $this->runner->addMigration($migration);
        }

        $this->runner->up();

        $this->assertSame('A', array_pop($container->result)); // Last Position = A

        // Any Order
        $this->assertContains('B', $container->result);
        $this->assertContains('C', $container->result);
        $this->assertContains('D', $container->result);
    }

    public function testUpWithMigrationWithDeepDependencies(): void
    {
        // Container
        $container = new StdClass();
        // Container Logger
        $container->result = [];

        $migrations = [
            $this->createMigration('A', ['B']),
            $this->createMigration('B', ['C', 'D']),
            $this->createMigration('C'),
            $this->createMigration('D'),
        ];

        foreach ($migrations as $migration) {
            $this->assertMigrationAssert($container, $migration);
            $this->assertMigrationUp($container, $migration);
            $this->runner->addMigration($migration);
        }

        $this->runner->up();

        $this->assertSame('A', array_pop($container->result));
        $this->assertSame('B', array_pop($container->result));

        // Any Order
        $this->assertContains('C', $container->result);
        $this->assertContains('D', $container->result);
    }

    public function testUpWithMigrationWithUnknownDependency(): void
    {
        $this->expectException(Exception::class);

        $migration = $this->createMigration('A', ['B']);

        $this->runner
            ->addMigration($migration)
            ->up();
    }

    public function testUpWithCircularDependencies(): void
    {
        $this->expectException(Exception::class);

        $migrationA = $this->createMigration('A', ['B']);
        $migrationB = $this->createMigration('B', ['A']);

        $this->runner
            ->addMigration($migrationA)
            ->addMigration($migrationB)
            ->up();
    }

    public function testUpWithSelfDependency(): void
    {
        $this->expectException(Exception::class);

        $migration = $this->createMigration('A', ['A']);

        $this->runner
            ->addMigration($migration)
            ->up();
    }
}
