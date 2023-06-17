<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Filters\ClassesFilter;

use IteratorAggregate;
use Micoli\SymfonyCartography\Model\AnalyzedCodeBase;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final class ClassesFilter
{
    /** @var IteratorAggregate<ClassFilterInterface> */
    private iterable $filters;

    /** @param IteratorAggregate<ClassFilterInterface> $filters */
    public function __construct(
        #[TaggedIterator(ClassFilterInterface::class)]
        iterable $filters,
    ) {
        $this->filters = $filters;
    }

    public function filterClasses(AnalyzedCodeBase $analyzedCodeBase): void
    {
        foreach ($analyzedCodeBase->enrichedClasses as $className => $enrichedClass) {
            if ($this->isFiltered($enrichedClass)) {
                $analyzedCodeBase->enrichedClasses->offsetUnset($className);
            }
        }
    }

    private function isFiltered(EnrichedClass $enrichedClass): bool
    {
        foreach ($this->filters as $filter) {
            if ($filter->isFiltered($enrichedClass)) {
                return true;
            }
        }

        return false;
    }
}
