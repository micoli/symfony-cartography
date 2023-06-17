<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Graph\Cytoscape;

use Micoli\SymfonyCartography\DataStructures\EnrichedClasses;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Model\EnrichedMethod;
use Micoli\SymfonyCartography\Model\MethodCall;
use Micoli\SymfonyCartography\Service\Graph\AbstractGraphGenerator;
use Micoli\SymfonyCartography\Service\Graph\GraphEngine;
use Micoli\SymfonyCartography\Service\Graph\GraphGeneratorInterface;
use Micoli\SymfonyCartography\Service\Graph\GraphIds;
use Micoli\SymfonyCartography\Service\Graph\GraphOptions;

final class CytoscapeGraphGenerator extends AbstractGraphGenerator implements GraphGeneratorInterface
{
    public static function getEngine(): GraphEngine
    {
        return GraphEngine::CYTOSCAPE;
    }

    public function data(EnrichedClasses $enrichedClasses, ?GraphOptions $graphOptions = null): array
    {
        $calls = [];
        $ids = new GraphIds();
        /**
         * @var EnrichedClass $class
         * @var EnrichedMethod $method
         * @var MethodCall $call
         */
        foreach ($enrichedClasses->getMethodCalls() as [$class, $method, $call]) {
            $calls[$call->getClass2classHash()] = [
                'from' => $ids->get($call->from->namespacedName),
                'to' => $ids->get($call->to->namespacedName),
                'label' => $call->label,
            ];
        }
        $classes = [];
        foreach ($enrichedClasses as $class) {
            $classes[] = [
                'id' => $ids->get($class->namespacedName),
                'label' => $class->namespacedName,
                'category' => $class->getCategory()->asText(),
            ];
        }

        return [
            'nodes' => $classes,
            'edges' => array_values($calls),
            'options' => $graphOptions,
            'colors' => $this->categoryColors,
        ];
    }
}
