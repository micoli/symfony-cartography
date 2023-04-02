<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Graph;

use Micoli\SymfonyCartography\DataStructures\EnrichedClasses;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Model\MethodCall;
use Micoli\SymfonyCartography\Model\MethodName;
use Micoli\SymfonyCartography\Service\Categorizer\ClassCategoryColoring;
use Micoli\SymfonyCartography\Service\Categorizer\ClassCategoryInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment;

final class PlantUmlGraphGenerator implements GraphGeneratorInterface
{
    private ClassCategoryColoring $categoryColors;

    /**
     * @param list<array{class: ClassCategoryInterface,color:string}> $categoryColors
     */
    public function __construct(
        private readonly Environment $environment,
        private readonly HttpClientInterface $httpClient,
        array $categoryColors,
    ) {
        $this->categoryColors = new ClassCategoryColoring(array_reduce(
            $categoryColors,
            /**
             * @param array<string, string> $accumulator
             * @param array{class: ClassCategoryInterface,color:string} $color
             */
            function (array $accumulator, array $color) {
                $accumulator[$color['class']->asText()] = $color['color'];

                return $accumulator;
            },
            [],
        ));
    }

    public function generate(EnrichedClasses $enrichedClasses, GraphOptions $graphOptions): string
    {
        $calls = [];
        /**
         * @var EnrichedClass $class
         * @var MethodName $method
         * @var MethodCall $call
         */
        foreach ($enrichedClasses->getMethodCalls() as [$class, $method, $call]) {
//            $idFromMethod = $this->getIdFromMethod($call->from);
//            $idToMethod = $this->getIdFromMethod($call->to);
//            $calls[$call->getClass2classHash()] = [
//                'from' => $idFromMethod,
//                'to' => $idToMethod,
//                'label' => $call->label,
//            ];
//            $calls[$call->getClass2classHash()] = [
//                'from' => $idFromMethod,
//                'to' => $idToMethod,
//                'label' => $call->label,
//            ];
            $calls[$call->getClass2classHash()] = [
                'from' => $call->from->namespacedName,
                'to' => $call->to->namespacedName,
                'label' => $call->label,
            ];
        }

        $template = $this->environment->createTemplate(
            <<<TEMPLATE
                @startuml
                skinparam ArrowThickness 3
                skinparam linetype polyline
                skinparam linetype ortho
                {% for className,class in enrichedClasses %}
                class {{className}} {{ colors.getColor(class.category) }} {
                    **{{class.category.asText}}**
                    {{class.getCommentsAsString}}
                    ---
                    {% if options.withMethodDisplay %}
                        {% for method in class.methods %}
                          {% if method.definedInternally %}+{% else %}-{% endif %} {{method.methodName.name}}()
                        {% endfor %}        
                    {% endif %}
                }
                {% endfor %}        

                {% for call in calls %}
                "{{call.from}}" ==> "{{call.to}}"
                {% endfor %}        
                @enduml
                TEMPLATE
        );
        // "{{call.from}}" ==> "{{call.to}}" {% if call.label is not null %}"{{call.label}}"{% endif %}
        return $template->render([
            'enrichedClasses' => $enrichedClasses,
            'calls' => $calls,
            'options' => $graphOptions,
            'colors' => $this->categoryColors,
        ]);
    }

    private function getIdFromMethod(MethodName $methodName): string
    {
        return sprintf(
            '%s::%s',
            $methodName->namespacedName,
            $methodName->name,
        );
    }

    public function source(EnrichedClasses $enrichedClasses): string
    {
        return $this->generate($enrichedClasses, new GraphOptions(false));
    }

    public function svg(EnrichedClasses $enrichedClasses): string
    {
        return $this->httpClient->request(
            'POST',
            'http://127.0.0.1:8080/svg/',
            [
                'body' => $this->source($enrichedClasses),
                'headers' => [
                    'Accept' => 'image/svg+xml',
                    'Content-Type' => 'text/plain',
                ],
            ],
        )->getContent(false);
    }
}
