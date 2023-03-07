<?php

declare(strict_types=1);

namespace App\Domain\Bus\Command;

use App\Infrastructure\Bus\Message\Command\AsyncCommandInterface;

final class CreateActionCommand implements AsyncCommandInterface
{
    public function __construct(
    ) {
    }
}
