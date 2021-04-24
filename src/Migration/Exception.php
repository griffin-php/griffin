<?php

declare(strict_types=1);

namespace Griffin\Migration;

use Exception as BaseException;

class Exception extends BaseException
{
    const NAME_UNKNOWN     = 1;
    const NAME_DUPLICATED  = 2;
    const CALLABLE_UNKNOWN = 4;
}
