<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Categorizer;

use IteratorAggregate;
use Micoli\SymfonyCartography\Model\AnalyzedCodeBase;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final class ClassCategorizer
{
    /** @var IteratorAggregate<ClassCategorizerInterface> */
    private iterable $categorizers;

    /** @param IteratorAggregate<ClassCategorizerInterface> $categorizers */
    public function __construct(
        #[TaggedIterator(ClassCategorizerInterface::class)] iterable $categorizers,
    ) {
        $this->categorizers = $categorizers;
    }

    public function categorizeClasses(AnalyzedCodeBase $analyzedCodeBase): void
    {
        foreach ($analyzedCodeBase->enrichedClasses as $enrichedClass) {
            $this->categorize($enrichedClass, $analyzedCodeBase);
        }
    }

    public function categorize(EnrichedClass $enrichedClass, AnalyzedCodeBase $analyzedCodeBase): void
    {
        foreach ($this->categorizers as $categorizer) {
            if ($categorizer->support($enrichedClass, $analyzedCodeBase)) {
                $categorizer->categorize($enrichedClass);

                return;
            }
        }
        $enrichedClass->setCategory(ClassCategory::undefined);
    }
}
