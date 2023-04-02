<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Categorizer\Coloring;

use Micoli\SymfonyCartography\Service\Categorizer\ClassCategoryInterface;
use ParseError;

final class ClassCategoryColoring
{
    /** @param array<string, ColorDTO> $colors */
    public function __construct(
        private readonly array $colors = [],
    ) {
    }

    public function getColor(ClassCategoryInterface $categoryEnum): ColorDTO
    {
        $value = $categoryEnum->getValue();
        if (!array_key_exists($value, $this->colors)) {
            throw new ParseError(sprintf('No colors defined for ClassCategory "%s"', $value));
        }

        return $this->colors[$value];
    }
}
