<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Categorizer\Coloring;

use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'never')]
final class ColorDTO
{
    public function __construct(
        public readonly string $foreground,
        public readonly string $background,
    ) {
    }
}
