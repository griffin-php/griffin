<?php

declare(strict_types=1);

namespace Griffin\Planner;

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
}
