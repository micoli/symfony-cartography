<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\TwigExtension;

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
            new TwigFunction('enriched_class_svg', [$this, 'enrichedClassSvg'], ['is_safe' => ['html']]),
            new TwigFunction('enriched_class_source', [$this, 'enrichedClassPlantuml'], []),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('camel_to_space', [$this, 'convertCamelCaseToHaveSpacesFilter']),
        ];
    }

    public function enrichedClassSvg(string $classname, bool $base64 = false): string
    {
        $enrichedClasses = $this->codeParser->analyse()->enrichedClasses;
        $this->codeBaseFilters->filterOrphans($enrichedClasses);
        $this->codeBaseFilters->filterFrom($enrichedClasses, $classname);
        if ($base64) {
            return sprintf('data:image/svg+xml;base64,%s', base64_encode($this->graphGenerator->svg($enrichedClasses)));
        }

        return $this->graphGenerator->svg($enrichedClasses);
    }

    public function enrichedClassPlantuml(string $classname): string
    {
        $enrichedClasses = $this->codeParser->analyse()->enrichedClasses;
        $this->codeBaseFilters->filterOrphans($enrichedClasses);
        $this->codeBaseFilters->filterFrom($enrichedClasses, $classname);

        return $this->graphGenerator->source($enrichedClasses);
    }

    public function convertCamelCaseToHaveSpacesFilter(string $camelCaseString): string
    {
        return preg_replace_callback(
            '/(([A-Z]{1}))/',
            fn ($matches) => ' ' . $matches[0],
            $camelCaseString,
        );
    }
}
