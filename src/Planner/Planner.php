<?php

declare(strict_types=1);

namespace Griffin\Planner;

use Griffin\Migration\MigrationInterface;

/**
 * Planner
 */
class Planner
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
}
