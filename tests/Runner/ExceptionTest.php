<?php

declare(strict_types=1);

namespace GriffinTest\Runner;

use Exception as BaseException;
use Griffin\Runner\Exception;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    protected function setUp(): void
    {
        $this->exception = new Exception();
    }

    public function testInstanceOf(): void
    {
        $this->assertInstanceOf(BaseException::class, $this->exception);
    }

    public function testCodes(): void
    {
        $this->assertSame(1, Exception::ROLLBACK_CIRCULAR);
    }
}
