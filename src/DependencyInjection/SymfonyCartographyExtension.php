<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DependencyInjection;

use Micoli\SymfonyCartography\Profiler\SymfonyCartographyCollector;
use Micoli\SymfonyCartography\Service\Categorizer\ClassCategoryInterface;
use Micoli\SymfonyCartography\Service\CodeBase\CodeBaseAnalyser;
use Micoli\SymfonyCartography\Service\CodeBase\Psalm\PsalmRunner;
use Micoli\SymfonyCartography\Service\Filters\ClassesFilter\CommonFilter as ClassCommonFilter;
use Micoli\SymfonyCartography\Service\Filters\MethodCallFilter\CommonFilter as MethodCallCommonFilter;
use Micoli\SymfonyCartography\Service\Graph\PlantUmlGraphGenerator;
use Micoli\SymfonyCartography\Service\Symfony\MessengerAnalyser;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class SymfonyCartographyExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        /**
         * @var array{
         *     enabled: true,
         *     sources: list<string>,
         *     colors: list<array{class: ClassCategoryInterface,color:string}>,
         *     messenger_dispatchers: list<array{class: class-string, method: string}>,
         *     graph: array{
         *         engine_uri: string,
         *         withMethodDisplay: bool,
         *         withMethodArrows: bool,
         *         leftToRightDirection: bool,
         *     },
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

        $symfonyCartographyCollectorDefinition = $container->getDefinition(SymfonyCartographyCollector::class);
        $symfonyCartographyCollectorDefinition->setArgument('$enabled', $mergedConfig['enabled']);

        $codeBaseAnalyserDefinition = $container->getDefinition(CodeBaseAnalyser::class);
        $codeBaseAnalyserDefinition->setArgument('$srcRoots', $mergedConfig['sources']);
        $codeBaseAnalyserDefinition->setArgument('$cache', new Reference('symfony-cartography-cache'));

        $plantUmlDefinition = $container->getDefinition(PlantUmlGraphGenerator::class);
        $plantUmlDefinition->setArgument('$categoryColorsParameter', $mergedConfig['colors']);
        $plantUmlDefinition->setArgument('$graphOptionsWithMethodDisplay', $mergedConfig['graph']['withMethodDisplay']);
        $plantUmlDefinition->setArgument('$graphOptionsWithMethodArrows', $mergedConfig['graph']['withMethodArrows']);
        $plantUmlDefinition->setArgument('$graphOptionsLeftToRightDirection', $mergedConfig['graph']['leftToRightDirection']);
        $plantUmlDefinition->setArgument('$plantUmlURI', $mergedConfig['graph']['engine_uri']);

        $classFiltersDefinition = $container->getDefinition(ClassCommonFilter::class);
        $classFiltersDefinition->setArgument('$rules', $mergedConfig['filters']['classes']['rules']);

        $methodCallFiltersDefinition = $container->getDefinition(MethodCallCommonFilter::class);
        $methodCallFiltersDefinition->setArgument('$excludeLoopbackCall', $mergedConfig['filters']['method_calls']['exclude_loopback']);
        $methodCallFiltersDefinition->setArgument('$rules', $mergedConfig['filters']['method_calls']['rules']);

        $messengerAnalyserDefinition = $container->getDefinition(MessengerAnalyser::class);
        $messengerAnalyserDefinition->setArgument('$dispatchers', $mergedConfig['messenger_dispatchers']);

        $psalmRunnerDefinition = $container->getDefinition(PsalmRunner::class);
        $psalmRunnerDefinition->setArgument('$cacheDir', new Parameter('kernel.cache_dir'));
    }

    public function getAlias(): string
    {
        return 'symfony_cartography';
    }
}
