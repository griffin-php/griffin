<?php

declare(strict_types=1);

namespace Griffin\Migration;

use Exception as BaseException;

class Exception extends BaseException
{
    const DUPLICATED = 1;
}
