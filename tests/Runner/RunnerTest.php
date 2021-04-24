<?php

declare(strict_types=1);

namespace GriffinTest\Runner;

use Exception as BaseException;
use Griffin\Runner\Exception;
use League\Event\EventDispatcher;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use StdClass;

class RunnerTest extends TestCase
{
    use RunnerTrait;

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
