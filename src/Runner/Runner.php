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
    public function __construct(?Planner $planner = null)
    {
        $this->planner = $planner ?? new Planner();
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
     * @param  $depth    Recursion Depth
     * @param  $names    Migration Names
     * @throws Throwable Migration Error
     * @return Fluent Interface
     */
    protected function runUp(int $depth, string ...$names): self
    {
        $visited    = new Container();
        $container  = $this->getPlanner()->up(...$names);
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
                    // Looping?
                    if ($depth === 2) {
                        throw new Exception(
                            sprintf('Circular Rollback Found'),
                            Exception::ROLLBACK_CIRCULAR,
                            $error,
                        );
                    }
                    // Error Found
                    $this->runDown(++$depth, ...$visited->getMigrationNames());
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
        return $this->runUp(0, ...$names);
    }

    /**
     * Run Migrations Down
     *
     * @param  $depth    Recursion Depth
     * @param  $names    Migration Names
     * @throws Throwable Migration Error
     * @return Fluent Interface
     */
    protected function runDown(int $depth, string ...$names): self
    {
        $visited    = new Container();
        $container  = $this->getPlanner()->down(...$names);
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
                    // Looping?
                    if ($depth === 2) {
                        throw new Exception(
                            sprintf('Circular Rollback Found'),
                            Exception::ROLLBACK_CIRCULAR,
                            $error,
                        );
                    }
                    // Error Found
                    $this->runUp(++$depth, ...$visited->getMigrationNames());
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
        return $this->runDown(0, ...$names);
    }
}
