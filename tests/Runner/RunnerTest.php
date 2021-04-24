<?php

declare(strict_types=1);

namespace GriffinTest\Runner;

use ArrayObject;
use Exception as BaseException;
use Griffin\Event;
use Griffin\Migration\Container;
use Griffin\Migration\MigrationInterface;
use Griffin\Planner\Planner;
use Griffin\Runner\Exception;
use Griffin\Runner\Runner;
use League\Event\EventDispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use StdClass;

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

    public function testUpDownLoopingRollback(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Exception::ROLLBACK_CIRCULAR);
        $this->expectExceptionMessage('Circular Rollback Found');

        $container = $this->runner->getPlanner()->getContainer();

        $migrationA = $this->createMigration('A');
        $migrationB = $this->createMigration('B', ['A']);
        $migrationC = $this->createMigration('C', ['B']);

        $status = new StdClass();

        $status->A = false;
        $status->B = false;
        $status->C = false;

        $status->counter = 0;

        foreach ([$migrationA, $migrationB, $migrationC] as $migration) {
            $name = $migration->getName();

            $migration
                ->method('assert')
                ->will($this->returnCallback(function () use ($status, $name) {
                    // Limit?
                    if ($status->counter === 30) {
                        // Stop It!
                        throw new RuntimeException('Looping Found!');
                    }
                    // Next Step
                    $status->counter++;

                    return ! $status->$name = ! $status->$name;
                }));

            $container->addMigration($migration);
        }

        $migrationC->method('up')
            ->will($this->throwException(new BaseException('Up Error on C', 123)));

        $migrationA->method('down')
            ->will($this->throwException(new BaseException('Down Error on A', 321)));

        $this->runner->up();
    }
}
