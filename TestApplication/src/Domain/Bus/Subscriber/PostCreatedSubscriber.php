<?php

declare(strict_types=1);

namespace App\Domain\Bus\Subscriber;

use App\Domain\Bus\Event\PostCreatedEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PostCreatedSubscriber
{
    public function __invoke(PostCreatedEvent $event): void
    {
    }
}
