<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Graph;

use Micoli\SymfonyCartography\DataStructures\EnrichedClasses;

interface GraphGeneratorInterface
{
    public function generate(EnrichedClasses $enrichedClasses, GraphOptions $graphOptions): string;

    public function svg(EnrichedClasses $enrichedClasses): string;
}
