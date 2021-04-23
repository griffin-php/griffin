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
     * Plan Up Migration Execution
     *
     * @return Griffin\Migration\Migration[] List of Migrations in Sequence
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function up(): array
    {
        return $this->container->getMigrations();
    }
}
