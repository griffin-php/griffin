<?php

declare(strict_types=1);

namespace GriffinTest\Runner\Runner;

use Griffin\Event\Migration\UpAfter;
use Griffin\Event\Migration\UpBefore;
use Griffin\Runner\Exception;
use Griffin\Runner\Runner;
use GriffinTest\Runner\MigrationTrait;
use League\Event\EventDispatcher;
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

        $this->assertSame(['B', 'A'], $container->up);
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

        $this->assertSame('A', array_pop($container->up)); // Last Position = A

        // Any Order
        $this->assertContains('B', $container->up);
        $this->assertContains('C', $container->up);
        $this->assertContains('D', $container->up);
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

        $this->assertSame('A', array_pop($container->up));
        $this->assertSame('B', array_pop($container->up));

        // Any Order
        $this->assertContains('C', $container->up);
        $this->assertContains('D', $container->up);
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

        $helper->before = false;
        $helper->after  = false;

        $dispatcher = new EventDispatcher();

        $dispatcher->subscribeTo(UpBefore::class, fn() => $helper->before = true);
        $dispatcher->subscribeTo(UpAfter::class, fn() => $helper->after = true);

        $migration = $this->createMigration('MIGRATION');

        $this->runner
            ->setEventDispatcher($dispatcher)
            ->addMigration($migration)
            ->up();

        $this->assertTrue($helper->before);
        $this->assertTrue($helper->after);
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
