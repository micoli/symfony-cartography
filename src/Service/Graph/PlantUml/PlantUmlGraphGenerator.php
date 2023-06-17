<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Graph\PlantUml;

use Micoli\SymfonyCartography\DataStructures\EnrichedClasses;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Model\EnrichedMethod;
use Micoli\SymfonyCartography\Model\MethodCall;
use Micoli\SymfonyCartography\Service\Categorizer\ClassCategoryInterface;
use Micoli\SymfonyCartography\Service\Graph\AbstractGraphGenerator;
use Micoli\SymfonyCartography\Service\Graph\GraphEngine;
use Micoli\SymfonyCartography\Service\Graph\GraphGeneratorInterface;
use Micoli\SymfonyCartography\Service\Graph\GraphOptions;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment;

final class PlantUmlGraphGenerator extends AbstractGraphGenerator implements GraphGeneratorInterface
{
    public static function getEngine(): GraphEngine
    {
        return GraphEngine::PLANTUML;
    }

    /**
     * @param list<array{
     *     class: ClassCategoryInterface,
     *     color: string
     * }> $categoryColorsParameter
     */
    public function __construct(
        Environment $environment,
        array $categoryColorsParameter,
        bool $graphOptionsWithMethodDisplay,
        bool $graphOptionsWithMethodArrows,
        bool $graphOptionsLeftToRightDirection,
        private readonly HttpClientInterface $httpClient,
        private readonly string $plantUmlURI,
    ) {
        parent::__construct(
            $environment,
            $categoryColorsParameter,
            $graphOptionsWithMethodDisplay,
            $graphOptionsWithMethodArrows,
            $graphOptionsLeftToRightDirection,
        );
    }

    public function data(EnrichedClasses $enrichedClasses, ?GraphOptions $graphOptions = null): array
    {
        $graphOptions ??= $this->graphOptions;
        $calls = [];
        /**
         * @var EnrichedClass $class
         * @var EnrichedMethod $method
         * @var MethodCall $call
         */
        foreach ($enrichedClasses->getMethodCalls() as [$class, $method, $call]) {
            if ($graphOptions->withMethodArrows) {
                $idFromMethod = $this->getIdFromMethod($call->from);
                $idToMethod = $this->getIdFromMethod($call->to);
                $calls[$call->getClass2classHash()] = [
                    'from' => $idFromMethod,
                    'to' => $idToMethod,
                    'label' => $call->label,
                ];
            } else {
                $calls[$call->getClass2classHash()] = [
                    'from' => $call->from->namespacedName,
                    'to' => $call->to->namespacedName,
                    'label' => $call->label,
                ];
            }
        }

        $plantUmlSource = $this->environment
            ->createTemplate($this->getTemplate($this))
            ->render([
                'enrichedClasses' => $enrichedClasses,
                'calls' => $calls,
                'options' => $graphOptions,
                'colors' => $this->categoryColors,
            ]);

        return [
            'svg' => $this->httpClient->request(
                'POST',
                $this->plantUmlURI,
                [
                    'body' => $plantUmlSource,
                    'headers' => [
                        'Accept' => 'image/svg+xml',
                        'Content-Type' => 'text/plain',
                    ],
                ],
            )->getContent(false),
        ];
    }

    public function html(array $classNames): string
    {
        $template = <<<EOT
                <div id="graph_network"></div>
                <script type="text/javascript">
                    const classNames = {{ classNames|json_encode|raw }};
                    const dataURL = {{ path('cartography_graph_data')|json_encode|raw }};
                    fetch(dataURL + '?' + new URLSearchParams({classNames: classNames.join(',')}))
                        .then(response => response.json())
                        .then(data => document.getElementById("graph_network").innerHTML=data.svg)
                    ;
                </script>
            EOT;

        return $this->environment
            ->createTemplate($template)
            ->render([
                'classNames' => $classNames,
            ]);
    }
}
