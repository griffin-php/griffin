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

    protected function createContainer(): StdClass
    {
        $container = new StdClass();

        $container->up = [];

        return $container;
    }

    protected function assertMigrationAssert(StdClass $container, MockObject $migration): void
    {
        $migration->expects($this->atLeast(1))
            ->method('assert')
            ->will($this->returnCallback(fn() => array_search($migration->getName(), $container->up) !== false));
    }

    protected function assertMigrationUp(StdClass $container, MockObject $migration): void
    {
        $callback = fn() => $container->up = array_merge($container->up, [$migration->getName()]);

        $migration->expects($this->once())
            ->method('up')
            ->will($this->returnCallback($callback));
    }

    protected function assertMigrationDown(StdClass $container, MockObject $migration): void
    {
        $callback = fn () => $container->up = array_diff($container->up, [$migration->getName()]);

        $migration->expects($this->once())
            ->method('down')
            ->will($this->returnCallback($callback));
    }
}
