<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Filters\ClassesFilter;

use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Service\Filters\RuleMatcher;

final class CommonFilter implements ClassFilterInterface
{
    /**
     * @param list<string> $rules
     */
    public function __construct(
        private readonly array $rules = [],
    ) {
    }

    public function isFiltered(EnrichedClass $enrichedClass): bool
    {
        return RuleMatcher::isFiltered($this->rules, $enrichedClass->namespacedName);
    }
}
