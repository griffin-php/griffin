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

    protected ?Closure $assertOperator = null;

    protected ?Closure $up = null;

    public function withName(string $name): self
    {
        $migration = clone($this);

        $migration->name = $name;

        return $migration;
    }

    public function getName(): string
    {
        return $this->name ?? self::class;
    }

    public function withAssert(callable $operator): self
    {
        $migration = clone($this);

        if (! $operator instanceof Closure) {
            $operator = Closure::fromCallable($operator);
        }

        $migration->assertOperator = $operator;

        return $migration;
    }

    public function getAssert(): ?callable
    {
        return $this->assertOperator;
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
        if (! $this->assertOperator) {
            throw new Exception();
        }

        return ($this->assertOperator)();
    }

    /**
     * @return string[]
     */
    public function depends(): array
    {
        return [];
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
