<?php

declare(strict_types=1);

namespace Griffin\Migration;

use Closure;

class Migration implements MigrationInterface
{
    protected bool $status = false;

    protected ?Closure $assert = null;

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function up(): void
    {
        $this->status = true;
    }

    public function down(): void
    {
        $this->status = false;
    }

    public function assert(): bool
    {
        return $this->status;
    }

    /**
     * @return string[]
     */
    public function depends(): array
    {
        return [];
    }

    public function getAssert(): ?callable
    {
        return $this->assert;
    }

    public function withAssert(callable $assert): self
    {
        $migration = clone($this);

        $migration->assert = $assert;

        return $migration;
    }
}
