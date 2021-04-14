<?php

declare(strict_types=1);

namespace GriffinTest\Migration;

/**
 * @SuppressWarnings(PHPMD.ShortMethodName)
 */
interface OperatorInterface
{
    public function operate(): mixed;

    public function __invoke(): mixed;
}
