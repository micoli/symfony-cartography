<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Categorizer;

use Micoli\SymfonyCartography\Model\AnalyzedCodeBase;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(ClassCategorizerInterface::class)]
interface ClassCategorizerInterface
{
    public function support(EnrichedClass $enrichedClass, AnalyzedCodeBase $analyzedCodeBase): bool;

    public function categorize(EnrichedClass $enrichedClass): void;
}
