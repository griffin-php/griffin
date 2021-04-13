<?php

declare(strict_types=1);

namespace GriffinTest\Migration;

class Operator
{
    public function noop(): void
    {
    }

    public function __invoke(): void
    {
    }
}
