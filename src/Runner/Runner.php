<?php

declare(strict_types=1);

namespace Griffin\Runner;

use Griffin\Migration\MigrationInterface;

class Runner
{
    /**
     * @var Griffin\Migration\Migration[]
     */
    protected array $migrations = [];

    /**
     * @return Griffin\Migration\Migration[]
     */
    public function getMigrations(): array
    {
        return $this->migrations;
    }

    public function addMigration(MigrationInterface $migration): self
    {
        $this->migrations[] = $migration;

        return $this;
    }
}