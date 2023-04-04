<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Graph;

use Micoli\SymfonyCartography\DataStructures\EnrichedClasses;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Model\MethodCall;
use Micoli\SymfonyCartography\Model\MethodName;
use Micoli\SymfonyCartography\Service\Categorizer\ClassCategoryInterface;
use Micoli\SymfonyCartography\Service\Categorizer\Coloring\ClassCategoryColoring;
use Micoli\SymfonyCartography\Service\Categorizer\Coloring\ColorDTO;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment;

final class PlantUmlGraphGenerator implements GraphGeneratorInterface
{
    private ClassCategoryColoring $categoryColors;
    private GraphOptions $graphOptions;

    /**
     * @param list<array{
     *     class: ClassCategoryInterface,
     *     color: string
     * }> $categoryColorsParameter
     */
    public function __construct(
        private readonly Environment $environment,
        private readonly HttpClientInterface $httpClient,
        private readonly string $plantUmlURI,
        array $categoryColorsParameter,
        bool $graphOptionsWithMethodDisplay,
        bool $graphOptionsWithMethodArrows,
        bool $graphOptionsLeftToRightDirection,
    ) {
        $this->graphOptions = new GraphOptions(
            $graphOptionsWithMethodDisplay,
            $graphOptionsWithMethodArrows,
            $graphOptionsLeftToRightDirection,
        );

        $this->categoryColors = new ClassCategoryColoring(array_reduce(
            $categoryColorsParameter,
            /**
             * @param array<string, ColorDTO> $accumulator
             * @param array{class: ClassCategoryInterface, color:string} $color
             */
            function (array $accumulator, array $color) {
                /** @var string $key */
                $key = $color['class']->asText();
                $accumulator[$key] = new ColorDTO(
                    $this->getForegroundColor($color['color']),
                    $color['color'],
                );

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

        $template = $this->environment->createTemplate(
            <<<TEMPLATE
                @startuml
                {% if options.leftToRightDirection %}left to right direction
                {% endif %}
                skinparam ArrowThickness 3
                skinparam linetype polyline
                skinparam linetype ortho
                {% for className,class in enrichedClasses %}
                class {{className}} {{ colors.getColor(class.category).background }};text:{{ colors.getColor(class.category).foreground[1:] }} {
                    <color:{{ colors.getColor(class.category).foreground }}>**{{class.category.asText}}**
                    {%if class.getCommentsAsString %}<color:{{ colors.getColor(class.category).foreground }}>{{ class.getCommentsAsString }}
                    {% endif %}
                    {% if options.withMethodDisplay %}
                        ---
                        {% for method in class.methods %}
                          <color:{{ colors.getColor(class.category).foreground }}>{% if method.definedInternally %}+{% else %}-{% endif %} {{method.methodName.name}}()
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

    public function source(EnrichedClasses $enrichedClasses, ?GraphOptions $graphOptions = null): string
    {
        return $this->generate($enrichedClasses, $graphOptions ?? $this->graphOptions);
    }

    public function svg(EnrichedClasses $enrichedClasses, ?GraphOptions $graphOptions = null): string
    {
        return $this->httpClient->request(
            'POST',
            $this->plantUmlURI,
            [
                'body' => $this->source($enrichedClasses, $graphOptions),
                'headers' => [
                    'Accept' => 'image/svg+xml',
                    'Content-Type' => 'text/plain',
                ],
            ],
        )->getContent(false);
    }

    /**
     * @psalm-suppress PossiblyNullArrayAccess
     * @psalm-suppress PossiblyUndefinedArrayOffset
     * @psalm-suppress PossiblyNullOperand
     */
    private function getForegroundColor(string $rgbColor): string
    {
        [$red, $green, $blue] = sscanf($rgbColor, '#%02x%02x%02x');
        if (((int) $red * 0.299 + (int) $green * 0.587 + (int) $blue * 0.114) > 186) {
            return '#000000';
        }

        return '#ffffff';
    }
}
