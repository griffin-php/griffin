<?php

declare(strict_types=1);

namespace GriffinTest\Runner;

use Griffin\Planner\Planner;
use Griffin\Runner\Runner;
use League\Event\EventDispatcher;
use PHPUnit\Framework\TestCase;

class RunnerTest extends TestCase
{
    use RunnerTrait;

    public function testConstructor(): void
    {
        $runner = new Runner();

        $this->assertInstanceof(Planner::class, $runner->getPlanner());
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

    public function testDryRun(): void
    {
        $this->assertFalse($this->runner->isDryRun());
        $this->assertSame($this->runner, $this->runner->setDryRun());
        $this->assertTrue($this->runner->isDryRun());
        $this->assertSame($this->runner, $this->runner->unsetDryRun());
        $this->assertFalse($this->runner->isDryRun());
    }
}
