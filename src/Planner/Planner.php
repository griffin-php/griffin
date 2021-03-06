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
    public function __construct(?Container $container = null)
    {
        $this->container = $container ?? new Container();
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
     * @param  $visited Migrations Already Visited
     * @param  $planned Migrations Already Planned
     * @param  $name    Current Migration Name
     * @return Fluent Interface
     */
    protected function planUp(Container $visited, Container $planned, string $name): self
    {
        $migration = $this->getContainer()->getMigration($name);

        $visited->addMigration($migration);

        foreach ($migration->getDependencies() as $dependency) {
            if (! is_string($dependency)) {
                throw new Exception(
                    sprintf('Invalid Migration "%s" Dependency Data Type: "%s"', $name, gettype($dependency)),
                    Exception::DEPENDENCY_INVALID,
                );
            }

            if ($visited->hasMigration($dependency)) {
                $path   = $visited->getMigrationNames();
                $path[] = $dependency;

                throw new Exception(
                    sprintf('Circular Dependency Found: "%s"', implode(', ', $path)),
                    Exception::DEPENDENCY_CIRCULAR,
                );
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
     * @param $names Migration Names
     * @return Migration Container in Sequence
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function up(string ...$names): Container
    {
        if (func_num_args() === 0) {
            $names = $this->getContainer()->getMigrationNames();
        }

        $planned = new Container();

        foreach ($names as $name) {
            $this->planUp(new Container(), $planned, $name);
        }

        return $planned;
    }

    /**
     * Get Dependents by Migration Name
     *
     * @param string $name Migration Name
     * @return string[] Expected Values
     */
    protected function getDependents(string $name): array
    {
        $dependents = [];

        foreach ($this->getContainer() as $migration) {
            foreach ($migration->getDependencies() as $dependency) {
                if (! is_string($dependency)) {
                    throw new Exception(
                        sprintf('Invalid Migration "%s" Dependency Data Type: "%s"', $name, gettype($dependency)),
                        Exception::DEPENDENCY_INVALID,
                    );
                }
            }

            // Resolve Unknown Dependents
            $resolver     = fn($dependency) => $this->getContainer()->getMigration($dependency)->getName();
            $dependencies = array_map($resolver, $migration->getDependencies());

            if (false !== array_search($name, $dependencies)) {
                array_push($dependents, $migration->getName());
            }
        }

        return $dependents;
    }

    /**
     * Plan Down Migration Execution by Name Recursively
     *
     * @param  $visited Migrations Already Visited
     * @param  $planned Migrations Already Planned
     * @param  $name    Current Migration Name
     * @return Fluent Interface
     */
    protected function planDown(Container $visited, Container $planned, string $name): self
    {
        $migration = $this->getContainer()->getMigration($name);

        $visited->addMigration($migration);

        foreach ($this->getDependents($name) as $dependent) {
            if ($visited->hasMigration($dependent)) {
                $path   = $visited->getMigrationNames();
                $path[] = $dependent;

                throw new Exception(
                    sprintf('Circular Dependency Found: "%s"', implode(', ', $path)),
                    Exception::DEPENDENCY_CIRCULAR,
                );
            }

            $this->planDown($visited, $planned, $dependent);
        }

        if (! $planned->hasMigration($name)) {
            $planned->addMigration($migration);
        }

        return $this;
    }

    /**
     * Plan Down Migration Execution
     *
     * @param $names Migration Names
     * @return Migration Container in Sequence
     */
    public function down(string ...$names): Container
    {
        if (func_num_args() === 0) {
            $names = $this->getContainer()->getMigrationNames();
        }

        $planned = new Container();

        foreach ($names as $name) {
            $this->planDown(new Container(), $planned, $name);
        }

        return $planned;
    }
}
