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
            $visited = $this->run($visited, $migration);
        }

        return $this;
    }

    /**
     * @param string[] $visited
     * @return string[]
     */
    protected function run(array $visited, MigrationInterface $migration): array
    {
        array_push($visited, $migration->getName());

        foreach ($migration->getDependencies() as $dependency) {
            if (array_search($dependency, $visited) !== false) {
                throw new Exception(); // Circular Dependency
            }

            if (! isset($this->migrations[$dependency])) {
                throw new Exception();
            }

            $this->run($visited, $this->migrations[$dependency]);
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

    public function down(): self
    {
        foreach ($this->migrations as $migration) {
            if ($migration->assert()) {
                $migration->down();
            }
        }

        return $this;
    }
}
