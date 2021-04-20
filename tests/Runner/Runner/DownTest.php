<?php

declare(strict_types=1);

namespace GriffinTest\Runner\Runner;

use Griffin\Event\Migration\DownAfter;
use Griffin\Event\Migration\DownBefore;
use Griffin\Runner\Exception;
use Griffin\Runner\Runner;
use GriffinTest\Runner\MigrationTrait;
use League\Event\EventDispatcher;
use PHPUnit\Framework\TestCase;
use StdClass;

class DownTest extends TestCase
{
    use MigrationTrait;

    protected function setUp(): void
    {
        $this->runner = new Runner();
    }

    public function testDown(): void
    {
        $this->assertSame($this->runner, $this->runner->down());
    }

    public function testDownWithMigration(): void
    {
        $container = $this->createContainer(['A']);

        $migration = $this->createMigration('A');

        $this->assertMigrationAssert($container, $migration);
        $this->assertMigrationDown($container, $migration);

        $this->runner->addMigration($migration)->down();
    }

    public function testDownWithMigrationWithDependency(): void
    {
        $container = $this->createContainer(['A', 'B']);

        $migrations = [
            $this->createMigration('B'),
            $this->createMigration('A', ['B']),
        ];

        foreach ($migrations as $migration) {
            $this->assertMigrationAssert($container, $migration);
            $this->assertMigrationDown($container, $migration);
            $this->runner->addMigration($migration);
        }

        $this->runner->down();

        $this->assertSame([], $container->status);
        $this->assertSame(['A', 'B'], $container->down); // Must Respect Order
    }

    public function testDownWithMigrationWithMultipleDependencies(): void
    {
        $container = $this->createContainer(['A', 'B', 'C', 'D']);

        $migrations = [
            $this->createMigration('D'),
            $this->createMigration('C'),
            $this->createMigration('B'),
            $this->createMigration('A', ['B', 'C', 'D']),
        ];

        foreach ($migrations as $migration) {
            $this->assertMigrationAssert($container, $migration);
            $this->assertMigrationDown($container, $migration);
            $this->runner->addMigration($migration);
        }

        $this->runner->down();

        $this->assertSame('A', array_shift($container->down));

        // Any Order
        $this->assertContains('B', $container->down);
        $this->assertContains('C', $container->down);
        $this->assertContains('D', $container->down);
    }

    public function testUpWithMigrationWithDeepDependencies(): void
    {
        $container = $this->createContainer(['A', 'B', 'C', 'D']);

        $migrations = [
            $this->createMigration('D'),
            $this->createMigration('C'),
            $this->createMigration('B', ['C', 'D']),
            $this->createMigration('A', ['B']),
        ];

        foreach ($migrations as $migration) {
            $this->assertMigrationAssert($container, $migration);
            $this->assertMigrationDown($container, $migration);
            $this->runner->addMigration($migration);
        }

        $this->runner->down();

        $this->assertSame('A', array_shift($container->down));
        $this->assertSame('B', array_shift($container->down));

        // Any Order
        $this->assertContains('C', $container->down);
        $this->assertContains('D', $container->down);
    }

    public function testDownWithMigrationWithUnknownDependency(): void
    {
        $this->expectException(Exception::class);

        $migration = $this->createMigration('A', ['B']);

        $this->runner
            ->addMigration($migration)
            ->down();
    }

    public function testDownWithCircularDependencies(): void
    {
        $this->expectException(Exception::class);

        $migrationA = $this->createMigration('A', ['B']);
        $migrationB = $this->createMigration('B', ['A']);

        $this->runner
            ->addMigration($migrationA)
            ->addMigration($migrationB)
            ->down();
    }

    public function testDownWithSelfDependency(): void
    {
        $this->expectException(Exception::class);

        $migration = $this->createMigration('A', ['A']);

        $this->runner
            ->addMigration($migration)
            ->down();
    }

    public function testDownWithDeepCircularDependencies(): void
    {
        $this->expectException(Exception::class);

        $migrationA = $this->createMigration('A', ['B']);
        $migrationB = $this->createMigration('B', ['C']);
        $migrationC = $this->createMigration('C', ['A']);

        $this->runner
            ->addMigration($migrationA)
            ->addMigration($migrationB)
            ->addMigration($migrationC)
            ->down();
    }

    public function testDownEventDispatcher(): void
    {
        $helper = new StdClass();

        $helper->before = false;
        $helper->after  = false;

        $dispatcher = new EventDispatcher();

        $dispatcher->subscribeTo(DownBefore::class, fn() => $helper->before = true);
        $dispatcher->subscribeTo(DownAfter::class, fn() => $helper->after = true);

        $container = $this->createContainer(['MIGRATION']);

        $migration = $this->createMigration('MIGRATION');

        $this->assertMigrationAssert($container, $migration);
        $this->assertMigrationDown($container, $migration);

        $this->runner
            ->setEventDispatcher($dispatcher)
            ->addMigration($migration)
            ->down();

        $this->assertTrue($helper->before);
        $this->assertTrue($helper->after);
    }
}
