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

        $container->result = [];

        return $container;
    }

    protected function assertMigrationAssert(StdClass $container, MockObject $migration): void
    {
        $migration->expects($this->atLeast(1))
            ->method('assert')
            ->will($this->returnCallback(fn() => array_search($migration->getName(), $container->result) !== false));
    }

    protected function assertMigrationUp(StdClass $container, MockObject $migration): void
    {
        $migration->expects($this->once())
            ->method('up')
            ->will($this->returnCallback(fn() => array_push($container->result, $migration->getName())));
    }
}
