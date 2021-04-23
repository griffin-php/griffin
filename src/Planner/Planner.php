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
}
