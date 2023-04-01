<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Filters\ClassesFilter;

use Micoli\SymfonyCartography\Model\EnrichedClass;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(ClassFilterInterface::class)]
interface ClassFilterInterface
{
    public function isFiltered(EnrichedClass $enrichedClass): bool;
}
