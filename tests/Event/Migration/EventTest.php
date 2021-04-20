<?php

declare(strict_types=1);

namespace GriffinTest\Event\Migration;

use Griffin\Event\Migration\AbstractEvent;
use Griffin\Migration\MigrationInterface;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    protected function setUp(): void
    {
        $this->migration = $this->createMock(MigrationInterface::class);

        $this->event = $this->getMockForAbstractClass(AbstractEvent::class, [$this->migration]);
    }

    public function testAbstractEvent(): void
    {
        $this->assertSame($this->migration, $this->event->getMigration());
    }
}
