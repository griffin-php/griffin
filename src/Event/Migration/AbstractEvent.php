<?php

declare(strict_types=1);

namespace Griffin\Event\Migration;

use Griffin\Migration\MigrationInterface;

abstract class AbstractEvent
{
    protected MigrationInterface $migration;

    public function __construct(MigrationInterface $migration)
    {
        $this->migration = $migration;
    }

    public function getMigration(): MigrationInterface
    {
        return $this->migration;
    }
}
