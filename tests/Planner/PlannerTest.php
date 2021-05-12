<?php

declare(strict_types=1);

namespace GriffinTest\Planner;

use Griffin\Migration\Container;
use Griffin\Planner\Planner;
use PHPUnit\Framework\TestCase;

class PlannerTest extends TestCase
{
    use PlannerTrait;

    public function testConstructor(): void
    {
        $planner = new Planner();

        $this->assertInstanceOf(Container::class, $planner->getContainer());
    }

    public function testContainer(): void
    {
        $this->assertSame($this->container, $this->planner->getContainer());
    }
}
