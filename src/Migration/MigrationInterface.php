<?php

declare(strict_types=1);

namespace Griffin\Migration;

interface MigrationInterface
{
    public function getName(): string;

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function up(): void;

    public function down(): void;

    public function assert(): bool;

    /**
     * @return string[]
     */
    public function depends(): array;
}
