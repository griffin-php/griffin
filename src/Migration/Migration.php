<?php

declare(strict_types=1);

namespace Griffin\Migration;

use Closure;

/**
 * @SuppressWarnings(PHPMD.ShortMethodName)
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Migration implements MigrationInterface
{
    protected ?string $name;

    protected bool $status = false;

    protected ?Closure $assert = null;

    protected ?Closure $up = null;

    public function getName(): string
    {
        return $this->name ?? self::class;
    }

    public function withName(string $name): self
    {
        $migration = clone($this);

        $migration->name = $name;

        return $migration;
    }

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

        if (! $assert instanceof Closure) {
            $assert = Closure::fromCallable($assert);
        }

        $migration->assert = $assert;

        return $migration;
    }

    public function getUp(): ?callable
    {
        return $this->up;
    }

    public function withUp(callable $up): self
    {
        $migration = clone($this);

        if (! $up instanceof Closure) {
            $up = Closure::fromCallable($up);
        }

        $migration->up = $up;

        return $migration;
    }
}
