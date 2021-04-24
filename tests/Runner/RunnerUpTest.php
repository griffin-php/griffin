<?php

declare(strict_types=1);

namespace GriffinTest\Runner;

use ArrayObject;
use Exception as BaseException;
use Griffin\Event;
use Griffin\Migration\Container;
use Griffin\Runner\Exception;
use League\Event\EventDispatcher;
use PHPUnit\Framework\TestCase;
use StdClass;

class RunnerUpTest extends TestCase
{
    use RunnerTrait;

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

    public function testUpDownUpLoopingRollback(): void
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
