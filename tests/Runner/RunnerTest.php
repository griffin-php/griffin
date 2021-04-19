<?php

declare(strict_types=1);

namespace GriffinTest\Runner;

use Griffin\Runner\Exception;
use Griffin\Runner\Runner;
use PHPUnit\Framework\TestCase;

class RunnerTest extends TestCase
{
    use MigrationTrait;

    protected function setUp(): void
    {
        $this->runner = new Runner();
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
}
