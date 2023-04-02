<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Graph;

use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'never')]
final class GraphOptions
{
    public function __construct(
        public readonly bool $withMethodDisplay = false,
        public readonly bool $withMethodArrows = false,
        public readonly bool $leftToRightDirection = false,
    ) {
    }
}
