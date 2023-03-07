<?php

declare(strict_types=1);

namespace App\Domain\Bus\Subscriber;

use App\Domain\Bus\Event\PostCreatedEvent;
use App\Domain\Bus\Event\UserCreatedEvent;
use App\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class EventsSubscriber
{
    public function __construct(private readonly PostRepositoryInterface $postRepository)
    {
    }

    public function __invoke(UserCreatedEvent|PostCreatedEvent $event): void
    {
        $this->postRepository->findBySearchQuery('lorem ipsum');
    }
}
