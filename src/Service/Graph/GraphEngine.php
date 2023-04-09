<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Graph;

enum GraphEngine: string
{
    case PLANTUML = 'plantuml';
    case VISJS = 'visjs';
    case CYTOSCAPE = 'cytoscape';
}
