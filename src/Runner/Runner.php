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
        return array_values($this->migrations);
    }

    public function addMigration(MigrationInterface $migration): self
    {
        $name = $migration->getName();

        if (isset($this->migrations[$name])) {
            throw new Exception();
        }

        $this->migrations[$name] = $migration;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function up(): self
    {
        foreach ($this->migrations as $migration) {
            if (! $migration->assert()) {
                $migration->up();
            }
        }

        return $this;
    }
}
