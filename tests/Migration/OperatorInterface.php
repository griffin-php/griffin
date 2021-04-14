<?php

declare(strict_types=1);

namespace GriffinTest\Migration;

/**
 * @SuppressWarnings(PHPMD.ShortMethodName)
 */
interface OperatorInterface
{
    public function up(): void;

    public function down(): void;

    public function assert(): bool;

    public function __invoke(): mixed;
}
