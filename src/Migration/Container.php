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
     * @return Griffin\Migration\MigrationInterface[] Migrations
     */
    public function getMigrations(): array
    {
        return array_values($this->migrations);
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
     * @throws Griffin\Migration\Exception Duplicated Migration
     * @return Fluent Interface
     */
    public function addMigration(MigrationInterface $migration): self
    {
        $name = $migration->getName();

        if ($this->hasMigration($name)) {
            throw new Exception('Duplicated Migration ' . $name);
        }

        $this->migrations[$name] = $migration;

        return $this;
    }

    /**
     * Retrieve a Migration
     *
     * @param $name Name
     * @throws Griffin\Migration\Exception Unknown Name
     * @return Expected Value
     */
    public function getMigration(string $name): MigrationInterface
    {
        if (! $this->hasMigration($name)) {
            throw new Exception();
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
