<?php

declare(strict_types=1);

namespace GriffinTest;

use Griffin\Trusty;
use PHPUnit\Framework\TestCase;

class TrustyTest extends TestCase
{
    protected function setUp(): void
    {
        $this->trusty = new Trusty();
    }

    public function testTrue(): void
    {
        $this->assertTrue($this->trusty->isTrue());
    }
}
