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

class RunnerDownTest extends TestCase
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

    public function testDownBasic(): void
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
                ->will($this->returnValue(true));

            $migration->expects($this->once())
                ->method('down')
                ->will($this->returnCallback(fn() => $helper->addMigration($migration)));

            $container->addMigration($migration);
        }

        $this->assertSame($this->runner, $this->runner->down());
        $this->assertSame(['A', 'C', 'B'], $helper->getMigrationNames());
    }

    public function testDownEvents(): void
    {
        $helper    = new ArrayObject();
        $container = $this->runner->getPlanner()->getContainer();

        $migrations = [
            $this->createMigration('A', ['B']),
            $this->createMigration('B'),
        ];

        foreach ($migrations as $migration) {
            $migration->method('assert')
                ->will($this->returnValue(true));

            $container->addMigration($migration);
        }

        $dispatcher = new EventDispatcher();

        $dispatcher->subscribeTo(
            Event\Migration\DownBefore::class,
            fn($event) => $helper->append(sprintf('BEFORE_%s', $event->getMigration()->getName())),
        );

        $dispatcher->subscribeTo(
            Event\Migration\DownAfter::class,
            fn($event) => $helper->append(sprintf('AFTER_%s', $event->getMigration()->getName())),
        );

        $this->runner
            ->setEventDispatcher($dispatcher)
            ->down();

        $this->assertSame(['BEFORE_A', 'AFTER_A', 'BEFORE_B', 'AFTER_B'], $helper->getArrayCopy());
    }

    public function testDownNamed(): void
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
                ->will($this->returnValue(true));

            $migration->expects($this->once())
                ->method('down');

            $container->addMigration($migration);
        }

        foreach ($migrationSetY as $migration) {
            $migration->expects($this->never())
                ->method('assert')
                ->will($this->returnValue(true));

            $migration->expects($this->never())
                ->method('down');

            $container->addMigration($migration);
        }

        $this->runner->down('C', 'E');
    }

    public function testDownRollback(): void
    {
        $this->expectException(BaseException::class);
        $this->expectExceptionCode(321);
        $this->expectExceptionMessage('!spO');

        $container = $this->runner->getPlanner()->getContainer();

        $migrationA = $this->createMigration('A', ['B']);
        $migrationB = $this->createMigration('B', ['C']);
        $migrationC = $this->createMigration('C');

        foreach ([$migrationA, $migrationB] as $migration) {
            $migration->expects($this->exactly(2))
                ->method('assert')
                ->will($this->onConsecutiveCalls(true, false));

            $migration->expects($this->once())
                ->method('down');

            $migration->expects($this->once())
                ->method('up');

            $container->addMigration($migration);
        }

        $migrationC->expects($this->exactly(2))
            ->method('assert')
            ->will($this->onConsecutiveCalls(true, false));

        $migrationC->expects($this->once())
            ->method('down')
            ->will($this->throwException(new BaseException('!spO', 321)));

        $container->addMigration($migrationC);

        $this->runner->down();
    }
}
