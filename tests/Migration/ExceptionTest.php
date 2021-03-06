<?php

declare(strict_types=1);

namespace GriffinTest\Migration;

use Exception as BaseException;
use Griffin\Migration\Exception;
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
        $this->assertSame(1, Exception::NAME_UNKNOWN);
        $this->assertSame(2, Exception::NAME_DUPLICATED);
        $this->assertSame(4, Exception::CALLABLE_UNKNOWN);
    }
}
