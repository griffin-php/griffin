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

    /**
     * @var string[]
     */
    protected ?array $dependencies = [];

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
     * @param string[] $dependencies
     */
    public function withDependencies(array $dependencies): self
    {
        $migration = clone($this);

        $migration->dependencies = $dependencies;

        return $migration;
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
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
            throw new Exception(
                'Unknown Callable: "assert"',
                Exception::CALLABLE_UNKNOWN,
            );
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
            throw new Exception(
                'Unknown Callable: "up"',
                Exception::CALLABLE_UNKNOWN,
            );
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
            throw new Exception(
                'Unknown Callable: "down"',
                Exception::CALLABLE_UNKNOWN,
            );
        }

        ($this->down)();
    }
}
