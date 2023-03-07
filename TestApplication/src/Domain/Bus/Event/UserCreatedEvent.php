<?php

declare(strict_types=1);

namespace App\Domain\Bus\Event;

use App\Infrastructure\Bus\Message\Event\AsyncEventInterface;

final class UserCreatedEvent implements AsyncEventInterface
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
    ) {
    }

    public static function create(): self
    {
        return new self(1, '');
    }
}
