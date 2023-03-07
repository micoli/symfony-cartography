<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus;

use App\Infrastructure\Bus\Message\Event\EventInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerEventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function dispatch(EventInterface $event): void
    {
        $this->bus->dispatch($event);
    }
}
