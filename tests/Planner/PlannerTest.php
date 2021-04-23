<?php

declare(strict_types=1);

namespace GriffinTest\Planner;

use Griffin\Migration\MigrationInterface;
use Griffin\Planner\Exception;
use Griffin\Planner\Planner;
use PHPUnit\Framework\TestCase;

class PlannerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->planner = new Planner();
    }

    protected function createMigration(string $name): MigrationInterface
    {
        $migration = $this->createMock(MigrationInterface::class);

        $migration->method('getName')
            ->will($this->returnValue($name));

        return $migration;
    }

    public function testMigrations(): void
    {
        $this->assertSame([], $this->planner->getMigrations());
        $this->assertFalse($this->planner->hasMigration('MIGRATION'));

        $migration = $this->createMigration('MIGRATION');

        $this->assertSame($this->planner, $this->planner->addMigration($migration));
        $this->assertTrue($this->planner->hasMigration('MIGRATION'));
    }

    public function testMigrationsDuplicated(): void
    {
        $this->expectException(Exception::class);

        $migrationA = $this->createMigration('MIGRATION');
        $migrationB = $this->createMigration('MIGRATION');

        $this->planner
            ->addMigration($migrationA)
            ->addMigration($migrationB);
    }
}
