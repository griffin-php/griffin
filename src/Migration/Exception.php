<?php

declare(strict_types=1);

namespace Griffin\Migration;

use Exception as BaseException;

class Exception extends BaseException
{
    const UNKNOWN    = 1;
    const DUPLICATED = 2;
}
