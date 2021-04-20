<?php

declare(strict_types=1);

namespace GriffinTest\Event\Migration;

use Griffin\Event\Migration\DownAfter;
use Griffin\Migration\MigrationInterface;
use PHPUnit\Framework\TestCase;

class DownAfterTest extends TestCase
{
    protected function setUp(): void
    {
        $this->migration = $this->createMock(MigrationInterface::class);

        $this->event = new DownAfter($this->migration);
    }

    public function testMigration(): void
    {
        $this->assertSame($this->migration, $this->event->getMigration());
    }
}
