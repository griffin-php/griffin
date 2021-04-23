<?php

declare(strict_types=1);

namespace GriffinTest\Event;

use Griffin\Event\DispatcherAwareTrait;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

class DispatcherAwareTest extends TestCase
{
    public function testDispatcher(): void
    {
        $aware = $this->getMockForTrait(DispatcherAwareTrait::class);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->assertNull($aware->getEventDispatcher());
        $this->assertSame($aware, $aware->setEventDispatcher($dispatcher));
        $this->assertSame($dispatcher, $aware->getEventDispatcher());
        $this->assertSame($aware, $aware->setEventDispatcher(null));
        $this->assertNull($aware->getEventDispatcher());
    }
}
