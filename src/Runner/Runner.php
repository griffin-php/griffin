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
            $this->run($migration);
        }

        return $this;
    }

    protected function run(MigrationInterface $migration): void
    {
        foreach ($migration->getDependencies() as $dependency) {
            if (! isset($this->migrations[$dependency])) {
                throw new Exception();
            }

            $this->run($this->migrations[$dependency]);
        }

        if (! $migration->assert()) {
            $migration->up();
        }
    }
}
