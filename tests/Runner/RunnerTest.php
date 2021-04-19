<?php

declare(strict_types=1);

namespace GriffinTest\Runner;

use Griffin\Migration\MigrationInterface;
use Griffin\Runner\Runner;
use PHPUnit\Framework\TestCase;

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

        $otherMigration   = $this->createMock(MigrationInterface::class);
        $anotherMigration = $this->createMock(MigrationInterface::class);

        $this->runner
            ->addMigration($otherMigration)
            ->addMigration($anotherMigration);

        $this->assertSame([$migration, $otherMigration, $anotherMigration], $this->runner->getMigrations());
    }
}
