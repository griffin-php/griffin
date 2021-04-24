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
     * @param  $container Migrations Container
     * @throws Throwable  Migration Error
     * @return Fluent Interface
     */
    protected function runUp(Container $container): self
    {
        $visited    = new Container();
        $dispatcher = $this->getEventDispatcher();

        foreach ($container as $migration) {
            if (! $migration->assert()) {
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
                    $this->runDown($visited);
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

    /**
     * Run Migrations Up
     *
     * @param  $names    Migration Names
     * @throws Throwable Migration Error
     * @return Fluent Interface
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function up(string ...$names): self
    {
        return $this->runUp($this->getPlanner()->up(...$names));
    }

    /**
     * Run Migrations Down
     *
     * @param  $container Migrations Container
     * @throws Throwable  Migration Error
     * @return Fluent Interface
     */
    protected function runDown(Container $container): self
    {
        $visited    = new Container();
        $dispatcher = $this->getEventDispatcher();

        foreach ($container as $migration) {
            if ($migration->assert()) {
                if ($dispatcher) {
                    $dispatcher->dispatch(new Event\Migration\DownBefore($migration));
                }
                try {
                    // Migrate!
                    $migration->down();
                    // Done!
                    $visited->addMigration($migration);
                } catch (Throwable $error) {
                    // Error Found
                    $this->runUp($visited);
                    // Show Errors
                    throw $error;
                }
                if ($dispatcher) {
                    $dispatcher->dispatch(new Event\Migration\DownAfter($migration));
                }
            }
        }

        return $this;
    }

    /**
     * Run Migrations Down
     *
     * @param  $names    Migration Names
     * @throws Throwable Migration Error
     * @return Fluent Interface
     */
    public function down(string ...$names): self
    {
        return $this->runDown($this->getPlanner()->down(...$names));
    }
}
