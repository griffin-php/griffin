<?php

declare(strict_types=1);

namespace GriffinTest\Runner;

use Griffin\Runner\Runner;
use PHPUnit\Framework\TestCase;

class RunnerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->runner = new Runner();
    }

    public function testMigrations(): void
    {
        $migrations = $this->runner->getMigrations();

        $this->assertSame([], $migrations);
    }
}
