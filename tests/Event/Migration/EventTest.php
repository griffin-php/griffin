<?php

declare(strict_types=1);

namespace GriffinTest\Event\Migration;

use Griffin\Event\Migration as MigrationEvent;
use Griffin\Migration\MigrationInterface;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testAbstractEvent(): void
    {
        $migration = $this->createMock(MigrationInterface::class);

        $event = $this->getMockForAbstractClass(MigrationEvent\AbstractEvent::class, [$migration]);

        $this->assertSame($migration, $event->getMigration());
    }

    /**
     * @return Griffin\Event\Migration\AbstractEvent[]
     */
    public function migrationProvider(): array
    {
        $migration = $this->createMock(MigrationInterface::class);

        return [
            [$migration, new MigrationEvent\DownAfter($migration)],
            [$migration, new MigrationEvent\DownBefore($migration)],
            [$migration, new MigrationEvent\UpAfter($migration)],
            [$migration, new MigrationEvent\UpBefore($migration)],
        ];
    }

    /**
     * @dataProvider migrationProvider
     */
    public function testEvent(MigrationInterface $migration, MigrationEvent\AbstractEvent $event): void
    {
        $this->assertSame($migration, $event->getMigration());
    }
}
