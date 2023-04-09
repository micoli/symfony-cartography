<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\TwigExtension;

use Micoli\SymfonyCartography\Model\MethodName;
use Micoli\SymfonyCartography\Service\Categorizer\ClassCategory;
use Micoli\SymfonyCartography\Service\CodeBase\CodeBaseAnalyser;
use Micoli\SymfonyCartography\Service\CodeBase\CodeBaseFilters;
use Micoli\SymfonyCartography\Service\Graph\GraphGeneratorInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

#[AutoconfigureTag('twig.extension')]
final class SymfonyCartographyExtension extends AbstractExtension
{
    public function __construct(
        private readonly CodeBaseAnalyser $codeParser,
        private readonly CodeBaseFilters $codeBaseFilters,
        private readonly GraphGeneratorInterface $graphGenerator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('cartography_collected_data', [$this, 'parseCollectedData'], []),
            new TwigFunction('enriched_class_html', [$this, 'enrichedClassHtml'], ['is_safe' => ['html']]),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('camel_to_space', [$this, 'convertCamelCaseToHaveSpacesFilter']),
        ];
    }

    public function enrichedClassHtml(array $classnames, bool $base64 = false): string
    {
        return $this->graphGenerator->html($classnames);
    }

    public function convertCamelCaseToHaveSpacesFilter(string $camelCaseString): string
    {
        return preg_replace_callback(
            '/(([A-Z]{1}))/',
            fn ($matches) => ' ' . $matches[0],
            $camelCaseString,
        );
    }

    /**
     * @param list<MethodName> $collectedControllers
     *
     * @return array{controllers:list<class-string>, statistics: array<string, int>}
     */
    public function parseCollectedData(array $collectedControllers): array
    {
        $analyzedCodeBase = $this->codeParser->analyse();

        $controllers = [];
        foreach ($collectedControllers as $controller) {
            foreach ($analyzedCodeBase->enrichedClasses as $enrichedClass) {
                if ($enrichedClass->getCategory()->getValue() !== ClassCategory::controller->getValue()) {
                    continue;
                }
                if ($controller->namespacedName === $enrichedClass->namespacedName) {
                    if (!in_array($enrichedClass->namespacedName, $controllers, true)) {
                        $controllers[] = $enrichedClass->namespacedName;
                    }
                }
            }
        }

        $statistics = [];
        if (count($controllers) === 1) {
            $this->codeBaseFilters->filterOrphans($analyzedCodeBase->enrichedClasses);
            $this->codeBaseFilters->filterFrom($analyzedCodeBase->enrichedClasses, $controllers);
            foreach ($analyzedCodeBase->enrichedClasses as $enrichedClass) {
                $category = $enrichedClass->getCategory()->asText();
                $count = array_key_exists($category, $statistics) ? $statistics[$category] : 0;
                $statistics[$category] = $count + 1;
            }
        }
        $this->codeBaseFilters->filterUnknownMethodCalls($analyzedCodeBase->enrichedClasses);

        return [
            'controllers' => $controllers,
            'statistics' => $statistics,
        ];
    }
}
