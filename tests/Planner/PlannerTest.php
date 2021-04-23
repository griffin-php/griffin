<?php

declare(strict_types=1);

namespace GriffinTest\Planner;

use Griffin\Planner\Planner;
use PHPUnit\Framework\TestCase;

class PlannerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->planner = new Planner();
    }

    public function testMigrations(): void
    {
        $this->assertSame([], $this->planner->getMigrations());
    }
}
