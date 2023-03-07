<?php

declare(strict_types=1);

namespace App\Domain\Bus\Listener;

use App\Domain\Bus\Event\PostCreatedEvent;

final class PostCreatedListener
{
    public function __invoke(PostCreatedEvent $event): void
    {
    }
}
