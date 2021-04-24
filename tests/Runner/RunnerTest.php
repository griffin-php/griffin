<?php

declare(strict_types=1);

namespace GriffinTest\Runner;

use Griffin\Migration\Container;
use Griffin\Planner\Planner;
use Griffin\Runner\Runner;
use PHPUnit\Framework\TestCase;

class RunnerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->planner   = new Planner($this->container);
        $this->runner    = new Runner($this->planner);
    }

    public function testPlanner(): void
    {
        $this->assertSame($this->planner, $this->runner->getPlanner());
    }
}
