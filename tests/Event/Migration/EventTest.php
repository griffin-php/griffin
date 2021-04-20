<?php

declare(strict_types=1);

namespace GriffinTest\Event\Migration;

use Griffin\Event\Migration as MigrationEvent;
use Griffin\Migration\MigrationInterface;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    protected function setUp(): void
    {
        $this->migration = $this->createMock(MigrationInterface::class);
    }

    public function testAbstractEvent(): void
    {
        $event = $this->getMockForAbstractClass(MigrationEvent\AbstractEvent::class, [$this->migration]);

        $this->assertSame($this->migration, $event->getMigration());
    }

    public function testDownAfter(): void
    {
        $this->assertInstanceOf(MigrationEvent\AbstractEvent::class, new MigrationEvent\DownAfter($this->migration));
    }

    public function testDownBefore(): void
    {
        $this->assertInstanceOf(MigrationEvent\AbstractEvent::class, new MigrationEvent\DownBefore($this->migration));
    }

    public function testUpAfter(): void
    {
        $this->assertInstanceOf(MigrationEvent\AbstractEvent::class, new MigrationEvent\UpAfter($this->migration));
    }

    public function testUpBefore(): void
    {
        $this->assertInstanceOf(MigrationEvent\AbstractEvent::class, new MigrationEvent\UpBefore($this->migration));
    }
}
