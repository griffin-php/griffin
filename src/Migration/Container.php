<?php

declare(strict_types=1);

namespace Griffin\Migration;

use ArrayIterator;
use Countable;
use Griffin\Migration\MigrationInterface;
use IteratorAggregate;
use Traversable;

/**
 * Migration Container
 */
class Container implements Countable, IteratorAggregate
{
    /**
     * Migrations
     * @var Griffin\Migration\MigrationInterface[]
     */
    protected array $migrations = [];

    /**
     * Retrieve Migrations
     *
     * @return Griffin\Migration\MigrationInterface[] Expected Values
     */
    public function getMigrations(): array
    {
        return array_values($this->migrations);
    }

    /**
     * Retrieve Migration Names
     *
     * @return string[] Expected Values
     */
    public function getMigrationNames(): array
    {
        return array_keys($this->migrations);
    }

    /**
     * Check if Migration Exists
     *
     * @param $name Name
     * @return Expected Value
     */
    public function hasMigration(string $name): bool
    {
        return isset($this->migrations[$name]);
    }

    /**
     * Add a Migration
     *
     * @param $migration Migration
     * @throws Griffin\Migration\Exception Duplicated Migration Name
     * @return Fluent Interface
     */
    public function addMigration(MigrationInterface $migration): self
    {
        $name = $migration->getName();

        if ($this->hasMigration($name)) {
            throw new Exception(
                sprintf('Duplicated Migration Name: "%s"', $name),
                Exception::NAME_DUPLICATED,
            );
        }

        $this->migrations[$name] = $migration;

        return $this;
    }

    /**
     * Retrieve a Migration
     *
     * @param $name Name
     * @throws Griffin\Migration\Exception Unknown Migration Name
     * @return Expected Value
     */
    public function getMigration(string $name): MigrationInterface
    {
        if (! $this->hasMigration($name)) {
            throw new Exception(
                sprintf('Unknown Migration Name: "%s"', $name),
                Exception::NAME_UNKNOWN,
            );
        }

        return $this->migrations[$name];
    }

    /**
     * Length of Migration Added
     *
     * @return Expected Value
     */
    public function count(): int
    {
        return count($this->migrations);
    }

    /**
     * Container Iterator
     *
     * @return Expected Value
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->migrations);
    }
}
