<?php

declare(strict_types=1);

namespace App\Domain\Bus\Event\TestClasses;

final class TestStatic1
{
    public static function create(): self
    {
        return new self();
    }
}
