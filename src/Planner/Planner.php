<?php

declare(strict_types=1);

namespace Griffin\Planner;

use Griffin\Migration\Container;

/**
 * Planner
 */
class Planner
{
    /**
     * Container
     */
    protected Container $container;

    /**
     * Default Constructor
     *
     * @param $container Container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Retrieve the Container
     *
     * @return Expected Value
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Capture Migration Names
     *
     * @param Griffin\Migration\MigrationInterface[] $migrations List of Migrations
     * @return string[] Expected Value
     */
    protected function getNames(array $migrations): array
    {
        return array_map(fn($migration) => $migration->getName(), $migrations);
    }

    /**
     * Plan Up Migration Execution by Name Recursively
     *
     * @param  $planned Migrations Already Planned
     * @param  $name    Current Migration Name
     * @return Fluent Interface
     */
    protected function planUp(Container $planned, string $name): self
    {
        $migration = $this->container->getMigration($name);

        foreach ($migration->getDependencies() as $dependency) {
            $planned->addMigration($this->container->getMigration($dependency));
        }

        if (! $planned->hasMigration($name)) {
            $planned->addMigration($migration);
        }

        return $this;
    }

    /**
     * Plan Up Migration Execution
     *
     * @return Migration Container in Sequence
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function up(): Container
    {
        $planned    = new Container();
        $migrations = $this->container->getMigrations();

        $names = $this->getNames($migrations);

        foreach ($names as $name) {
            $this->planUp($planned, $name);
        }

        return $planned;
    }
}
