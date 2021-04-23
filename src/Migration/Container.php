<?php

declare(strict_types=1);

namespace Griffin\Migration;

use Griffin\Migration\MigrationInterface;

/**
 * Migration Container
 */
class Container
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
        return $this->migrations;
    }

    /**
     * Check if Migration Exists
     *
     * @param $name Name
     * @return Value Expected
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
            throw new Exception();
        }

        $this->migrations[$name] = $migration;

        return $this;
    }

    /**
     * Retrieve a Migration
     *
     * @param $name Name
     * @throws Griffin\Migration\Exception Unknown Name
     * @return Value Expected
     */
    public function getMigration(string $name): MigrationInterface
    {
        if (! $this->hasMigration($name)) {
            throw new Exception();
        }

        return $this->migrations[$name];
    }
}
