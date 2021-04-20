<?php

declare(strict_types=1);

namespace GriffinTest\Event\Migration;

use Griffin\Event\Migration\UpBefore;
use Griffin\Migration\MigrationInterface;
use PHPUnit\Framework\TestCase;

class UpBeforeTest extends TestCase
{
    protected function setUp(): void
    {
        $this->migration = $this->createMock(MigrationInterface::class);

        $this->event = new UpBefore($this->migration);
    }

    public function testMigration(): void
    {
        $this->assertSame($this->migration, $this->event->getMigration());
    }
}
