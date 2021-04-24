<?php

declare(strict_types=1);

namespace GriffinTest\Planner;

use PHPUnit\Framework\TestCase;

class PlannerTest extends TestCase
{
    use PlannerTrait;

    public function testContainer(): void
    {
        $this->assertSame($this->container, $this->planner->getContainer());
    }
}
