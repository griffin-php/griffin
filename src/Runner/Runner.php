<?php

declare(strict_types=1);

namespace Griffin\Runner;

use Griffin\Planner\Planner;

/**
 * Runner
 */
class Runner
{
    /**
     * Planner
     */
    protected Planner $planner;

    /**
     * Default Constructor
     *
     * @param $planner Planner
     */
    public function __construct(Planner $planner)
    {
        $this->planner = $planner;
    }

    /**
     * Retrieve Planner
     *
     * @return Expected Value
     */
    public function getPlanner(): Planner
    {
        return $this->planner;
    }

    /**
     * Run Migrations Up
     *
     * @return Fluent Interface
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function up(): self
    {
        $container = $this->planner->up();

        foreach ($container as $migration) {
            if (! $migration->assert()) {
                $migration->up();
            }
        }

        return $this;
    }
}
