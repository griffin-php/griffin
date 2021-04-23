<?php

declare(strict_types=1);

namespace Griffin\Runner;

use Griffin\Event\DispatcherAwareTrait;
use Griffin\Event\Migration as MigrationEvent;
use Griffin\Migration\MigrationInterface;

class Runner
{
    use DispatcherAwareTrait;

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

    public function getMigration(string $name): MigrationInterface
    {
        if (! $this->hasMigration($name)) {
            throw new Exception();
        }

        return $this->migrations[$name];
    }

    public function hasMigration(string $name): bool
    {
        return isset($this->migrations[$name]);
    }

    public function addMigration(MigrationInterface $migration): self
    {
        $name = $migration->getName();

        if ($this->hasMigration($name)) {
            throw new Exception();
        }

        foreach ($migration->getDependencies() as $dependency) {
            if (! is_string($dependency)) {
                throw new Exception();
            }
        }

        $this->migrations[$name] = $migration;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function up(string ...$names): self
    {
        if (func_num_args() === 0) {
            $names = array_keys($this->migrations);
        }

        $visited = [];

        foreach ($names as $name) {
            $visited = $this->migrationUp($visited, $name);
        }

        return $this;
    }

    /**
     * @param string[] $visited
     * @return string[]
     */
    protected function migrationUp(array $visited, string $name): array
    {
        if (! $this->hasMigration($name)) {
            throw new Exception();
        }

        array_push($visited, $name);

        $migration = $this->getMigration($name);

        foreach ($migration->getDependencies() as $dependency) {
            if (array_search($dependency, $visited) !== false) {
                throw new Exception(); // Circular Dependency
            }

            $this->migrationUp($visited, $dependency);
        }

        if (! $migration->assert()) {
            $eventDispatcher = $this->getEventDispatcher();

            if ($eventDispatcher) {
                $eventDispatcher->dispatch(new MigrationEvent\UpBefore($migration));
            }

            $migration->up();

            if ($eventDispatcher) {
                $eventDispatcher->dispatch(new MigrationEvent\UpAfter($migration));
            }
        }

        return $visited;
    }

    /**
     * @return Griffin\Migration\Migration[]
     */
    protected function getDependents(MigrationInterface $migration): array
    {
        return array_keys(array_filter(
            $this->migrations,
            fn($current) => array_search($migration->getName(), $current->getDependencies()) !== false
        ));
    }

    protected function check(): void
    {
        $dependencies = array_values(array_map(fn($migration) => $migration->getDependencies(), $this->migrations));
        $migrations   = array_keys($this->migrations);
        $everything   = array_merge($migrations, ...$dependencies);

        $unknown = array_diff($everything, $migrations);

        if ($unknown) {
            throw new Exception();
        }
    }

    public function down(string ...$names): self
    {
        if (func_num_args() === 0) {
            $names = array_keys($this->migrations);
        }

        $this->check();

        $visited = [];

        foreach ($names as $name) {
            $visited = $this->migrationDown($visited, $name);
        }

        return $this;
    }

    /**
     * @param string[] $visited
     * @return string[]
     */
    protected function migrationDown(array $visited, string $name): array
    {
        if (! $this->hasMigration($name)) {
            throw new Exception();
        }

        array_push($visited, $name);

        $migration = $this->getMigration($name);

        foreach ($this->getDependents($migration) as $dependent) {
            if (array_search($dependent, $visited) !== false) {
                throw new Exception(); // Circular Dependency
            }

            $this->migrationDown($visited, $dependent);
        }

        if ($migration->assert()) {
            $eventDispatcher = $this->getEventDispatcher();

            if ($eventDispatcher) {
                $eventDispatcher->dispatch(new MigrationEvent\DownBefore($migration));
            }

            $migration->down();

            if ($eventDispatcher) {
                $eventDispatcher->dispatch(new MigrationEvent\DownAfter($migration));
            }
        }

        return $visited;
    }
}
