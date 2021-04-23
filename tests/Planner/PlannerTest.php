<?php

declare(strict_types=1);

namespace GriffinTest\Planner;

use Griffin\Migration\Container as MigrationContainer;
use Griffin\Planner\Planner;
use PHPUnit\Framework\TestCase;

class PlannerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = $this->createMock(MigrationContainer::class);

        $this->planner = new Planner($this->container);
    }

    public function testContainer(): void
    {
        $this->assertSame($this->container, $this->planner->getContainer());
    }
}
