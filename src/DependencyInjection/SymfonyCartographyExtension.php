<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DependencyInjection;

use Micoli\SymfonyCartography\Service\Categorizer\ClassCategoryInterface;
use Micoli\SymfonyCartography\Service\CodeBase\CodeBaseAnalyser;
use Micoli\SymfonyCartography\Service\Filters\ClassesFilter\CommonFilter as ClassCommonFilter;
use Micoli\SymfonyCartography\Service\Filters\MethodCallFilter\CommonFilter as MethodCallCommonFilter;
use Micoli\SymfonyCartography\Service\Graph\PlantUmlGraphGenerator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class SymfonyCartographyExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        /**
         * @var array{
         *     sources: list<string>,
         *     colors: list<array{class: ClassCategoryInterface,color:string}>,
         *     filters: array{
         *         classes: array{
         *             rules: list<string>
         *         },
         *         method_calls: array{
         *             exclude_loopback: bool,
         *             rules: list<string>
         *         }
         *     }
         * } $mergedConfig
         */
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../Resources/config'));
        $loader->load('services.yaml');

        $codeBaseAnalyserDefinition = $container->getDefinition(CodeBaseAnalyser::class);
        $codeBaseAnalyserDefinition->setArgument('$srcRoots', $mergedConfig['sources']);

        $plantUmlDefinition = $container->getDefinition(PlantUmlGraphGenerator::class);
        $plantUmlDefinition->setArgument('$categoryColors', $mergedConfig['colors']);

        $classFiltersDefinition = $container->getDefinition(ClassCommonFilter::class);
        $classFiltersDefinition->setArgument('$rules', $mergedConfig['filters']['classes']['rules']);

        $methodCallFiltersDefinition = $container->getDefinition(MethodCallCommonFilter::class);
        $methodCallFiltersDefinition->setArgument('$excludeLoopbackCall', $mergedConfig['filters']['method_calls']['exclude_loopback']);
        $methodCallFiltersDefinition->setArgument('$rules', $mergedConfig['filters']['method_calls']['rules']);
    }

    public function getAlias(): string
    {
        return 'symfony_cartography';
    }
}
