<?php

declare(strict_types=1);

namespace GriffinTest\Runner;

use Griffin\Migration\MigrationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use StdClass;

trait MigrationTrait
{
    /**
     * @param string[] $dependencies
     */
    protected function createMigration(string $name, array $dependencies = []): MigrationInterface
    {
        $migration = $this->createMock(MigrationInterface::class);

        $migration->method('getName')
            ->will($this->returnValue($name));

        $migration->method('getDependencies')
            ->will($this->returnValue($dependencies));

        return $migration;
    }

    /**
     * @param string[] $status
     */
    protected function createContainer(array $status = []): StdClass
    {
        $container = new StdClass();

        $container->up   = []; // up called
        $container->down = []; // down called

        $container->status = $status; // current status

        return $container;
    }

    protected function assertMigrationAssert(StdClass $container, MockObject $migration): void
    {
        $migration->expects($this->atLeast(1))
            ->method('assert')
            ->will($this->returnCallback(fn() => array_search($migration->getName(), $container->status) !== false));
    }

    protected function assertMigrationUp(StdClass $container, MockObject $migration): void
    {
        $callback = function () use ($container, $migration): void {
            $container->status = array_merge($container->status, [$migration->getName()]);
            $container->up     = array_merge($container->up, [$migration->getName()]);
        };

        $migration->expects($this->once())
            ->method('up')
            ->will($this->returnCallback($callback));
    }

    protected function assertMigrationDown(StdClass $container, MockObject $migration): void
    {
        $callback = function () use ($container, $migration): void {
            $container->status = array_diff($container->status, [$migration->getName()]);
            $container->down   = array_merge($container->down, [$migration->getName()]);
        };

        $migration->expects($this->once())
            ->method('down')
            ->will($this->returnCallback($callback));
    }
}
