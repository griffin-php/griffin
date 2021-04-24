<?php

declare(strict_types=1);

namespace Griffin\Planner;

use Exception as BaseException;

class Exception extends BaseException
{
    const DEPENDENCY_CIRCULAR = 1;
}
