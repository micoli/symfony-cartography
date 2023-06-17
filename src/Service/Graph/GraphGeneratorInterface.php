<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Graph;

use Micoli\SymfonyCartography\DataStructures\EnrichedClasses;

interface GraphGeneratorInterface
{
    public static function getEngine(): GraphEngine;

    public function html(array $classNames): string;

    public function data(EnrichedClasses $enrichedClasses, GraphOptions $graphOptions = null): array;
}
