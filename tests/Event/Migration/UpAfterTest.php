<?php

declare(strict_types=1);

namespace GriffinTest\Event\Migration;

use Griffin\Event\Migration\UpAfter;
use Griffin\Migration\MigrationInterface;
use PHPUnit\Framework\TestCase;

class UpAfterTest extends TestCase
{
    protected function setUp(): void
    {
        $this->migration = $this->createMock(MigrationInterface::class);

        $this->event = new UpAfter($this->migration);
    }

    public function testMigration(): void
    {
        $this->assertSame($this->migration, $this->event->getMigration());
    }
}
