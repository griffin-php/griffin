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
     * Plan Up Migration Execution by Name Recursively
     *
     * @param  $planned Migrations Already Planned
     * @param  $name    Current Migration Name
     * @return Fluent Interface
     */
    protected function planUp(Container $visited, Container $planned, string $name): self
    {
        $migration = $this->container->getMigration($name);

        $visited->addMigration($migration);

        foreach ($migration->getDependencies() as $dependency) {
            if ($visited->hasMigration($dependency)) {
                throw new Exception("Circular Dependency"); // Circular Dependency
            }

            $this->planUp($visited, $planned, $dependency);
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
        $planned = new Container();

        foreach ($this->getContainer()->getMigrationNames() as $name) {
            $this->planUp(new Container(), $planned, $name);
        }

        return $planned;
    }
}
