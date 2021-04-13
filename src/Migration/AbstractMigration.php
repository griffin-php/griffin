<?php

declare(strict_types=1);

namespace Griffin\Migration;

abstract class AbstractMigration
{
    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    abstract public function up(): void;

    abstract public function down(): void;

    abstract public function assert(): bool;
}
