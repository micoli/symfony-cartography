<?php

declare(strict_types=1);

namespace App\Domain\Bus\Event;

use App\Infrastructure\Bus\Message\Event\AsyncEventInterface;

final class UserPasswordUpdatedEvent implements AsyncEventInterface
{
    public function __construct(
        public readonly int $id,
    ) {
    }
}
