<?php

declare(strict_types=1);

namespace GriffinTest\Runner;

use ArrayObject;
use Exception as BaseException;
use Griffin\Event;
use Griffin\Migration\Container;
use Griffin\Migration\MigrationInterface;
use Griffin\Planner\Planner;
use Griffin\Runner\Runner;
use League\Event\EventDispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RunnerUpTest extends TestCase
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

    public function testUpBasic(): void
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
            ->addMigration($this->createMigration('A', ['B']))
            ->addMigration($this->createMigration('B'));

        $dispatcher = new EventDispatcher();

        $dispatcher->subscribeTo(
            Event\Migration\UpBefore::class,
            fn($event) => $helper->append(sprintf('BEFORE_%s', $event->getMigration()->getName())),
        );

        $dispatcher->subscribeTo(
            Event\Migration\UpAfter::class,
            fn($event) => $helper->append(sprintf('AFTER_%s', $event->getMigration()->getName())),
        );

        $this->runner
            ->setEventDispatcher($dispatcher)
            ->up();

        $this->assertSame(['BEFORE_B', 'AFTER_B', 'BEFORE_A', 'AFTER_A'], $helper->getArrayCopy());
    }

    public function testUpNamed(): void
    {
        $container = $this->runner->getPlanner()->getContainer();

        $migrationSetX = [
            $this->createMigration('A', ['C']),
            $this->createMigration('C'),
            $this->createMigration('E'),
        ];

        $migrationSetY = [
            $this->createMigration('B', ['D']),
            $this->createMigration('D'),
        ];

        foreach ($migrationSetX as $migration) {
            $migration->expects($this->atLeast(1))
                ->method('assert')
                ->will($this->returnValue(false));

            $migration->expects($this->once())
                ->method('up');

            $container->addMigration($migration);
        }

        foreach ($migrationSetY as $migration) {
            $migration->expects($this->never())
                ->method('assert')
                ->will($this->returnValue(false));

            $migration->expects($this->never())
                ->method('up');

            $container->addMigration($migration);
        }

        $this->runner->up('A', 'E');
    }

    public function testUpRollback(): void
    {
        $this->expectException(BaseException::class);
        $this->expectExceptionCode(123);
        $this->expectExceptionMessage('Ops!');

        $container = $this->runner->getPlanner()->getContainer();

        $migrationA = $this->createMigration('A', ['B']);
        $migrationB = $this->createMigration('B', ['C']);
        $migrationC = $this->createMigration('C');

        $migrationA->expects($this->exactly(2))
            ->method('assert')
            ->will($this->onConsecutiveCalls(false, true));

        $migrationA->expects($this->once())
            ->method('up')
            ->will($this->throwException(new BaseException('Ops!', 123)));

        $container->addMigration($migrationA);

        foreach ([$migrationB, $migrationC] as $migration) {
            $migration->expects($this->exactly(2))
                ->method('assert')
                ->will($this->onConsecutiveCalls(false, true));

            $migration->expects($this->once())
                ->method('up');

            $migration->expects($this->once())
                ->method('down');

            $container->addMigration($migration);
        }

        $this->runner->up();
    }
}
