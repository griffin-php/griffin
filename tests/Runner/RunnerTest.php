<?php

declare(strict_types=1);

namespace GriffinTest\Runner;

use Griffin\Migration\MigrationInterface;
use Griffin\Runner\Exception;
use Griffin\Runner\Runner;
use PHPUnit\Framework\TestCase;
use StdClass;

class RunnerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->runner = new Runner();
    }

    public function testMigrations(): void
    {
        $this->assertSame([], $this->runner->getMigrations());

        $migration = $this->createMock(MigrationInterface::class);

        $this->assertSame($this->runner, $this->runner->addMigration($migration));
        $this->assertSame([$migration], $this->runner->getMigrations());

        $otherMigration = $this->createMock(MigrationInterface::class);

        $otherMigration->method('getName')
            ->will($this->returnValue('MIGRATION_OTHER'));

        $anotherMigration = $this->createMock(MigrationInterface::class);

        $anotherMigration->method('getName')
            ->will($this->returnValue('MIGRATION_ANOTHER'));

        $this->runner
            ->addMigration($otherMigration)
            ->addMigration($anotherMigration);

        $this->assertSame([$migration, $otherMigration, $anotherMigration], $this->runner->getMigrations());
    }

    public function testMigrationsDuplicated(): void
    {
        $this->expectException(Exception::class);

        $migrationOne = $this->createMock(MigrationInterface::class);

        $migrationOne->method('getName')
            ->will($this->returnValue('MIGRATION'));

        $migrationTwo = $this->createMock(MigrationInterface::class);

        $migrationTwo->method('getName')
            ->will($this->returnValue('MIGRATION')); // Same Name from $migrationOne

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
        $migration = $this->createMock(MigrationInterface::class);

        $migration->expects($this->atLeast(1))
            ->method('assert')
            ->will($this->returnValue(false));

        $migration->expects($this->once(1))
            ->method('up');

        $this->runner->addMigration($migration)->up();
    }

    public function testUpWithMigrationWithDependency(): void
    {
        $this->markTestIncomplete();

        // Container
        $container = new StdClass();
        // Container Logger
        $container->result = [];

        // Migration A
        $migrationA = $this->createMock(MigrationInterface::class);

        $migrationA->expects($this->atLeast(1))
            ->method('assert')
            ->will($this->returnValue(false));

        $migrationA->expects($this->once())
            ->method('up')
            ->will($this->returnCallback(fn() => $container->result[] = 'A'));

        $migrationA->method('getDependencies')
            ->will($this->returnValue(['MIGRATION_B']));

        // Migration B
        $migrationB = $this->createMock(MigrationInterface::class);

        $migrationB->expects($this->atLeast(1))
            ->method('assert')
            ->will($this->returnValue(false));

        $migrationB->expects($this->once())
            ->method('up')
            ->will($this->returnCallback(fn() => $container->result[] = 'B'));

        $migrationB->method('getName')
            ->will($this->returnValue('MIGRATION_B'));

        // Running
        $this->runner
            ->addMigration($migrationA)
            ->addMigration($migrationB)
            ->up();

        $this->assertSame(['B', 'A'], $container->result);
    }
}
