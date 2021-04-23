<?php

declare(strict_types=1);

namespace GriffinTest\Migration;

use Griffin\Migration\Container;
use Griffin\Migration\Exception;
use Griffin\Migration\MigrationInterface;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = new Container();
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
        $this->assertSame([], $this->container->getMigrations());
        $this->assertFalse($this->container->hasMigration('MIGRATION'));

        $migration = $this->createMigration('MIGRATION');

        $this->assertSame($this->container, $this->container->addMigration($migration));
        $this->assertTrue($this->container->hasMigration('MIGRATION'));
    }

    public function testMigrationsDuplicated(): void
    {
        $this->expectException(Exception::class);

        $migrationA = $this->createMigration('MIGRATION');
        $migrationB = $this->createMigration('MIGRATION');

        $this->container
            ->addMigration($migrationA)
            ->addMigration($migrationB);
    }
}
