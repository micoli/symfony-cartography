<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Graph;

use Micoli\SymfonyCartography\Model\MethodName;
use Micoli\SymfonyCartography\Service\Categorizer\ClassCategoryInterface;
use Micoli\SymfonyCartography\Service\Categorizer\Coloring\ClassCategoryColoring;
use Micoli\SymfonyCartography\Service\Categorizer\Coloring\ColorDTO;
use Twig\Environment;

abstract class AbstractGraphGenerator
{
    protected ClassCategoryColoring $categoryColors;
    protected GraphOptions $graphOptions;

    /**
     * @param list<array{
     *     class: ClassCategoryInterface,
     *     color: string
     *  }> $categoryColorsParameter
     */
    public function __construct(
        protected readonly Environment $environment,
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

    public function html(array $classNames): string
    {
        return $this->environment
            ->createTemplate($this->getTemplate($this))
            ->render(['classNames' => $classNames]);
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

    /**
     * @psalm-suppress InvalidArrayOffset
     * @psalm-suppress MixedArgument
     */
    protected function getTemplate(GraphGeneratorInterface|AbstractGraphGenerator $instance): string
    {
        $parts = explode('\\', $instance::class);

        return file_get_contents(sprintf(
            '%s/%s/%s.html.twig',
            __DIR__,
            $parts[count($parts) - 2],
            $parts[count($parts) - 1],
        ));
    }

    protected function getIdFromMethod(MethodName $methodName): string
    {
        return sprintf(
            '%s::%s',
            $methodName->namespacedName,
            $methodName->name,
        );
    }
}
