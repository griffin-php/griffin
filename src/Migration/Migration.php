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

    protected ?Closure $upOperator = null;

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

    public function assert(): bool
    {
        if (! $this->assertOperator) {
            throw new Exception();
        }

        return ($this->assertOperator)();
    }

    public function withUp(callable $operator): self
    {
        $migration = clone($this);

        if (! $operator instanceof Closure) {
            $operator = Closure::fromCallable($operator);
        }

        $migration->upOperator = $operator;

        return $migration;
    }

    public function up(): void
    {
        ($this->upOperator)();
    }

    public function down(): void
    {
        $this->status = false;
    }

    /**
     * @return string[]
     */
    public function depends(): array
    {
        return [];
    }
}
