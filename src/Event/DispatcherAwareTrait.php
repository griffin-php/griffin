<?php

declare(strict_types=1);

namespace Griffin\Event;

use Psr\EventDispatcher\EventDispatcherInterface;

trait DispatcherAwareTrait
{
    protected ?EventDispatcherInterface $dispatcher = null;

    public function setEventDispatcher(?EventDispatcherInterface $dispatcher): self
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    public function getEventDispatcher(): ?EventDispatcherInterface
    {
        return $this->dispatcher;
    }
}
