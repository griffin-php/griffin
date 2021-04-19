<?php

declare(strict_types=1);

namespace GriffinTest\Runner\Runner;

use Griffin\Runner\Runner;
use PHPUnit\Framework\TestCase;

class DownTest extends TestCase
{
    protected function setUp(): void
    {
        $this->runner = new Runner();
    }

    public function testDown(): void
    {
        $this->assertSame($this->runner, $this->runner->down());
    }
}
