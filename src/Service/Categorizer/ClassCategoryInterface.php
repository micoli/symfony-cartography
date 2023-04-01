<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Categorizer;

interface ClassCategoryInterface
{
    public function asText(): string;

    public function getValue(): string;
}
