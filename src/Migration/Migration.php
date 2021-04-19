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

    protected ?Closure $assert = null;

    protected ?Closure $up = null;

    protected ?Closure $down = null;

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

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [];
    }

    public function withAssert(callable $operator): self
    {
        $migration = clone($this);

        if (! $operator instanceof Closure) {
            $operator = Closure::fromCallable($operator);
        }

        $migration->assert = $operator;

        return $migration;
    }

    public function assert(): bool
    {
        if (! $this->assert) {
            throw new Exception();
        }

        return ($this->assert)();
    }

    public function withUp(callable $operator): self
    {
        $migration = clone($this);

        if (! $operator instanceof Closure) {
            $operator = Closure::fromCallable($operator);
        }

        $migration->up = $operator;

        return $migration;
    }

    public function up(): void
    {
        if (! $this->up) {
            throw new Exception();
        }

        ($this->up)();
    }

    public function withDown(callable $operator): self
    {
        $migration = clone($this);

        if (! $operator instanceof Closure) {
            $operator = Closure::fromCallable($operator);
        }

        $migration->down = $operator;

        return $migration;
    }

    public function down(): void
    {
        if (! $this->down) {
            throw new Exception();
        }

        ($this->down)();
    }
}
