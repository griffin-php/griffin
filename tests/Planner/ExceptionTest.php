<?php

declare(strict_types=1);

namespace GriffinTest\Planner;

use Exception as BaseException;
use Griffin\Planner\Exception;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $this->assertInstanceOf(BaseException::class, new Exception());
    }
}
