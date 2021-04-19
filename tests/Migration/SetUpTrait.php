<?php

declare(strict_types=1);

namespace GriffinTest\Migration;

use Griffin\Migration\Migration;

trait SetUpTrait
{
    protected function setUp(): void
    {
        $this->migration = new Migration();
    }
}
