<?php

declare(strict_types=1);

namespace App\Domain\Bus\Event;

use App\Infrastructure\Bus\Message\Event\AsyncEventInterface;

final class CalledMethodEvent implements AsyncEventInterface
{
    public function __construct(
    ) {
    }

    public static function dispatch(mixed ...$args): void
    {
    }
}
