<?php

declare(strict_types=1);

namespace App\Domain\Bus\Subscriber;

use App\Domain\Bus\Event\PostCreatedEvent;
use App\Domain\Bus\Event\UserCreatedEvent;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/** @psalm-suppress DeprecatedInterface */
final class EventsSubscriberOldSchool implements MessageSubscriberInterface
{
    public function onUserCreatedEvent(UserCreatedEvent $event): void
    {
    }

    public function onPostCreatedEvent(PostCreatedEvent $event): void
    {
    }

    public static function getHandledMessages(): iterable
    {
        yield PostCreatedEvent::class => [
            'method' => 'onUserCreatedEvent',
        ];
        yield PostCreatedEvent::class => [
            'method' => 'onPostCreatedEvent',
        ];
    }
}
