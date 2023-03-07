<?php

declare(strict_types=1);

namespace App\Domain\Bus\Subscriber;

use App\Domain\Bus\Command\CreateActionCommand;
use App\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateActionHandler
{
    public function __construct(private readonly PostRepositoryInterface $postRepository)
    {
    }

    public function __invoke(CreateActionCommand $event): void
    {
        $this->postRepository->findBySearchQuery('lorem ipsum');
    }
}
