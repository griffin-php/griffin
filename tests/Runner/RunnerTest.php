<?php

declare(strict_types=1);

namespace GriffinTest\Runner;

use Griffin\Migration\Container;
use Griffin\Migration\MigrationInterface;
use Griffin\Planner\Planner;
use Griffin\Runner\Runner;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RunnerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->planner   = new Planner($this->container);
        $this->runner    = new Runner($this->planner);
    }

    /**
     * @param string[] $dependencies
     */
    protected function createMigration(string $name, array $dependencies = []): MockObject
    {
        $migration = $this->createMock(MigrationInterface::class);

        $migration->method('getName')
            ->will($this->returnValue($name));

        $migration->method('getDependencies')
            ->will($this->returnValue($dependencies));

        return $migration;
    }

    public function testPlanner(): void
    {
        $this->assertSame($this->planner, $this->runner->getPlanner());
    }

    public function testBasic(): void
    {
        $container = $this->runner->getPlanner()->getContainer();

        $migrations = [
            $this->createMigration('A', ['B']),
            $this->createMigration('B'),
            $this->createMigration('C', ['B']),
        ];

        foreach ($migrations as $migration) {
            $migration->expects($this->atLeast(1))
                ->method('assert')
                ->will($this->returnValue(false));

            $migration->expects($this->once())
                ->method('up');

            $container->addMigration($migration);
        }

        $this->assertSame($this->runner, $this->runner->up());
    }
}
