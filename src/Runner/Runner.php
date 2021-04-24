<?php

declare(strict_types=1);

namespace Griffin\Runner;

use Griffin\Event;
use Griffin\Migration\Container;
use Griffin\Planner\Planner;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

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
     * Event Dispatcher PSR-14
     */
    protected ?EventDispatcherInterface $eventDispatcher = null;

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
     * Configure Event Dispatcher
     *
     * @param Event Dispatcher
     * @return Fluent Interface
     */
    public function setEventDispatcher(?EventDispatcherInterface $eventDispatcher): self
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * Retrieve Event Dispatcher
     *
     * @return Event Dispatcher
     */
    public function getEventDispatcher(): ?EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * Run Migrations Up
     *
     * @return Fluent Interface
     * @param $names Migration Names
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function up(string ...$names): self
    {
        $visited   = new Container();
        $container = $this->getPlanner()->up(...$names);

        foreach ($container as $migration) {
            if (! $migration->assert()) {
                $dispatcher = $this->getEventDispatcher();

                if ($dispatcher) {
                    $dispatcher->dispatch(new Event\Migration\UpBefore($migration));
                }

                try {
                    // Migrate!
                    $migration->up();
                    // Done!
                    $visited->addMigration($migration);
                } catch (Throwable $error) {
                    // Error Found
                    foreach ($visited as $migration) {
                        if ($migration->assert()) {
                            // Rollback
                            $migration->down(); // TODO $this->down();
                        }
                    }
                    // Show Errors
                    throw $error;
                }

                if ($dispatcher) {
                    $dispatcher->dispatch(new Event\Migration\UpAfter($migration));
                }
            }
        }

        return $this;
    }
}
