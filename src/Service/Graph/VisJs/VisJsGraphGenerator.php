<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Graph\VisJs;

use Micoli\SymfonyCartography\DataStructures\EnrichedClasses;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Model\MethodCall;
use Micoli\SymfonyCartography\Model\MethodName;
use Micoli\SymfonyCartography\Service\ClassHelper;
use Micoli\SymfonyCartography\Service\Graph\AbstractGraphGenerator;
use Micoli\SymfonyCartography\Service\Graph\GraphEngine;
use Micoli\SymfonyCartography\Service\Graph\GraphGeneratorInterface;
use Micoli\SymfonyCartography\Service\Graph\GraphIds;
use Micoli\SymfonyCartography\Service\Graph\GraphOptions;

final class VisJsGraphGenerator extends AbstractGraphGenerator implements GraphGeneratorInterface
{
    public static function getEngine(): GraphEngine
    {
        return GraphEngine::VISJS;
    }

    public function data(EnrichedClasses $enrichedClasses, ?GraphOptions $graphOptions = null): array
    {
        $calls = [];
        $ids = new GraphIds();

        /**
         * @var EnrichedClass $class
         * @var MethodName $method
         * @var MethodCall $call
         */
        foreach ($enrichedClasses->getMethodCalls() as [$class, $method, $call]) {
            $calls[$call->getClass2classHash()] = [
                'from' => $ids->get($call->from->namespacedName),
                'to' => $ids->get($call->to->namespacedName),
                'font' => [
                    'multi' => true,
                ],
                'label' => $call->label ? sprintf(
                    "<b>%s</b>\n%s",
                    ClassHelper::extractClassname($call->label),
                    ClassHelper::extractNamespace($call->label),
                ) : null,
            ];
        }
        $classes = [];
        foreach ($enrichedClasses as $class) {
            $classes[] = [
                'id' => $ids->get($class->namespacedName),
                'label' => sprintf(
                    "<b>%s</b>\n%s",
                    $class->name,
                    $class->getNamespace(),
                ),
                'shape' => 'box',
                'font' => [
                    'color' => $this->categoryColors->getColor($class->getCategory())->foreground,
                    'multi' => true,
                ],
                'color' => $this->categoryColors->getColor($class->getCategory())->background,
            ];
        }

        return [
            'nodes' => $classes,
            'edges' => array_values($calls),
            'options' => $graphOptions,
        ];
    }
}
