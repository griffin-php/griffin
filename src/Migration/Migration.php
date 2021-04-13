<?php

declare(strict_types=1);

namespace Griffin\Migration;

class Migration implements MigrationInterface
{
    protected bool $assert = false;

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function up(): void
    {
        $this->assert = true;
    }

    public function down(): void
    {
        $this->assert = false;
    }

    public function assert(): bool
    {
        return $this->assert;
    }

    /**
     * @return string[]
     */
    public function depends(): array
    {
        return [];
    }
}
