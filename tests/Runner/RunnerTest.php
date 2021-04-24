<?php

declare(strict_types=1);

namespace GriffinTest\Runner;

use Exception as BaseException;
use League\Event\EventDispatcher;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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
}
