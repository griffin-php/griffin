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
        $this->assertSame($migration, $this->container->getMigration('MIGRATION'));
        $this->assertSame([$migration], $this->container->getMigrations());

        $this->container
            ->addMigration($this->createMigration('FOOBAR'))
            ->addMigration($this->createMigration('BAZQUX'));

        $this->assertCount(3, $this->container->getMigrations());
        $this->assertCount(3, $this->container); // Countable

        $this->assertSame(['MIGRATION', 'FOOBAR', 'BAZQUX'], $this->container->getMigrationNames());
    }

    public function testMigrationsDuplicated(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::DUPLICATED);
        $this->expectExceptionMessage('Duplicated Migration Name: "MIGRATION"');

        $migrationA = $this->createMigration('MIGRATION');
        $migrationB = $this->createMigration('MIGRATION');

        $this->container
            ->addMigration($migrationA)
            ->addMigration($migrationB);
    }

    public function testMigrationUnknown(): void
    {
        $this->expectException(Exception::class);

        $this->container->getMigration('UNKNOWN');
    }

    public function testMigrationAllowUnknownDependency(): void
    {
        // Everything must Work
        $this->expectNotToPerformAssertions();

        $this->container->addMigration($this->createMigration('A', ['B']));
    }
}
