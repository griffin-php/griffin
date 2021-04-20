<?php

declare(strict_types=1);

namespace GriffinTest\Runner\Runner;

use Griffin\Event\Migration\UpBefore;
use Griffin\Runner\Exception;
use Griffin\Runner\Runner;
use GriffinTest\Runner\MigrationTrait;
use PHPUnit\Framework\TestCase;
use StdClass;

class UpTest extends TestCase
{
    use MigrationTrait;

    protected function setUp(): void
    {
        $this->runner = new Runner();
    }

    public function testUp(): void
    {
        $this->assertSame($this->runner, $this->runner->up());
    }

    public function testUpWithMigration(): void
    {
        $container = $this->createContainer();

        $migration = $this->createMigration('A');

        $this->assertMigrationAssert($container, $migration);
        $this->assertMigrationUp($container, $migration);

        $this->runner->addMigration($migration)->up();
    }

    public function testUpWithMigrationWithDependency(): void
    {
        $container = $this->createContainer();

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
        $container = $this->createContainer();

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
        $container = $this->createContainer();

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

    public function testUpWithDeepCircularDependencies(): void
    {
        $this->expectException(Exception::class);

        $migrationA = $this->createMigration('A', ['B']);
        $migrationB = $this->createMigration('B', ['C']);
        $migrationC = $this->createMigration('C', ['A']);

        $this->runner
            ->addMigration($migrationA)
            ->addMigration($migrationB)
            ->addMigration($migrationC)
            ->up();
    }

    public function testUpEventDispatcher(): void
    {
        $helper = new StdClass();

        $helper->status = false;

        $this->runner->getEventDispatcher()
            ->subscribeTo(UpBefore::class, fn() => $helper->status = true);

        $migration = $this->createMigration('MIGRATION');

        $this->runner
            ->addMigration($migration)
            ->up();

        $this->assertTrue($helper->status);
    }
}
