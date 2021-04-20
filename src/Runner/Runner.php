<?php

declare(strict_types=1);

namespace Griffin\Runner;

use Griffin\Event\Migration\UpAfter;
use Griffin\Event\Migration\UpBefore;
use Griffin\Migration\MigrationInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class Runner
{
    protected ?EventDispatcherInterface $eventDispatcher = null;

    /**
     * @var Griffin\Migration\Migration[]
     */
    protected array $migrations = [];

    public function setEventDispatcher(?EventDispatcherInterface $eventDispatcher): self
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    public function getEventDispatcher(): ?EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

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
        $visited = [];

        foreach ($this->migrations as $migration) {
            $visited = $this->migrationUp($visited, $migration);
        }

        return $this;
    }

    /**
     * @param string[] $visited
     * @return string[]
     */
    protected function migrationUp(array $visited, MigrationInterface $migration): array
    {
        array_push($visited, $migration->getName());

        foreach ($migration->getDependencies() as $dependency) {
            if (array_search($dependency, $visited) !== false) {
                throw new Exception(); // Circular Dependency
            }

            if (! isset($this->migrations[$dependency])) {
                throw new Exception();
            }

            $this->migrationUp($visited, $this->migrations[$dependency]);
        }

        if (! $migration->assert()) {
            $eventDispatcher = $this->getEventDispatcher();

            if ($eventDispatcher) {
                $eventDispatcher->dispatch(new UpBefore($migration));
            }

            $migration->up();

            if ($eventDispatcher) {
                $eventDispatcher->dispatch(new UpAfter($migration));
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

    protected function migrationDown(MigrationInterface $migration): void
    {
        foreach ($this->getDependents($migration) as $dependent) {
            $this->migrationDown($this->migrations[$dependent]);
        }

        if ($migration->assert()) {
            $migration->down();
        }
    }

    public function down(): self
    {
        $this->check();

        foreach ($this->migrations as $migration) {
            $this->migrationDown($migration);
        }

        return $this;
    }
}
