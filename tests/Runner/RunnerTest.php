<?php

declare(strict_types=1);

namespace GriffinTest\Runner;

use ArrayObject;
use Griffin\Event;
use Griffin\Migration\Container;
use Griffin\Migration\MigrationInterface;
use Griffin\Planner\Planner;
use Griffin\Runner\Runner;
use League\Event\EventDispatcher;
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

    public function testEventDispatcher(): void
    {
        $dispatcher = new EventDispatcher();

        $this->assertNull($this->runner->getEventDispatcher());
        $this->assertSame($this->runner, $this->runner->setEventDispatcher($dispatcher));
        $this->assertSame($dispatcher, $this->runner->getEventDispatcher());
        $this->assertSame($this->runner, $this->runner->setEventDispatcher(null));
        $this->assertNull($this->runner->getEventDispatcher());
    }

    public function testBasic(): void
    {
        $helper    = new Container();
        $container = $this->runner->getPlanner()->getContainer();

        $migrations = [
            $this->createMigration('A', ['C']),
            $this->createMigration('B'),
            $this->createMigration('C', ['B']),
        ];

        foreach ($migrations as $migration) {
            $migration->expects($this->atLeast(1))
                ->method('assert')
                ->will($this->returnValue(false));

            $migration->expects($this->once())
                ->method('up')
                ->will($this->returnCallback(fn() => $helper->addMigration($migration)));

            $container->addMigration($migration);
        }

        $this->assertSame($this->runner, $this->runner->up());
        $this->assertSame(['B', 'C', 'A'], $helper->getMigrationNames());
    }

    public function testUpEvents(): void
    {
        $helper = new ArrayObject();

        $this->runner->getPlanner()->getContainer()
            ->addMigration($this->createMigration('A'));

        $dispatcher = new EventDispatcher();

        $dispatcher->subscribeTo(Event\Migration\UpBefore::class, fn() => $helper->append('BEFORE'));
        $dispatcher->subscribeTo(Event\Migration\UpAfter::class, fn() => $helper->append('AFTER'));

        $this->runner
            ->setEventDispatcher($dispatcher)
            ->up();

        $this->assertSame(['BEFORE', 'AFTER'], $helper->getArrayCopy());
    }
}
