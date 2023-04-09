<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DependencyInjection;

use Micoli\SymfonyCartography\Profiler\SymfonyCartographyCollector;
use Micoli\SymfonyCartography\Service\Categorizer\ClassCategoryInterface;
use Micoli\SymfonyCartography\Service\CodeBase\CodeBaseAnalyser;
use Micoli\SymfonyCartography\Service\CodeBase\Psalm\PsalmRunner;
use Micoli\SymfonyCartography\Service\Filters\ClassesFilter\CommonFilter as ClassCommonFilter;
use Micoli\SymfonyCartography\Service\Filters\MethodCallFilter\CommonFilter as MethodCallCommonFilter;
use Micoli\SymfonyCartography\Service\Graph\Cytoscape\CytoscapeGraphGenerator;
use Micoli\SymfonyCartography\Service\Graph\GraphEngine;
use Micoli\SymfonyCartography\Service\Graph\GraphGeneratorInterface;
use Micoli\SymfonyCartography\Service\Graph\PlantUml\PlantUmlGraphGenerator;
use Micoli\SymfonyCartography\Service\Graph\VisJs\VisJsGraphGenerator;
use Micoli\SymfonyCartography\Service\Symfony\MessengerAnalyser;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * @psalm-type MergedConfig = array{
 *     enabled: true,
 *     sources: list<string>,
 *     colors: list<array{class: ClassCategoryInterface,color:string}>,
 *     messenger_dispatchers: list<array{class: class-string, method: string}>,
 *     graph: array{
 *         engine: string,
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
 * }
 */
final class SymfonyCartographyExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        /**
         * @var MergedConfig $mergedConfig
         */
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../Resources/config'));
        $loader->load('services.yaml');

        $this->initSymfonyCartographyCollector($container, $mergedConfig);
        $this->initCodebaseAnalyser($container, $mergedConfig);
        $this->initPlantUmlGraphGenerator($container, $mergedConfig);
        $this->initCytoscapeGraphGenerator($container, $mergedConfig);
        $this->initVisJsGraphGenerator($container, $mergedConfig);
        $this->initClassFilters($container, $mergedConfig);
        $this->initMethodCallFilters($container, $mergedConfig);
        $this->initMessengerAnalyser($container, $mergedConfig);
        $this->initPsalmRunner($container);
        $container->setAlias(GraphGeneratorInterface::class, match ($mergedConfig['graph']['engine']) {
            GraphEngine::PLANTUML->value => PlantUmlGraphGenerator::class,
            GraphEngine::VISJS->value => VisJsGraphGenerator::class,
            GraphEngine::CYTOSCAPE->value => CytoscapeGraphGenerator::class,
        });
    }

    public function getAlias(): string
    {
        return 'symfony_cartography';
    }

    public function initPsalmRunner(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(PsalmRunner::class);
        $definition->setArgument('$cacheDir', new Parameter('kernel.cache_dir'));
    }

    /**
     * @param MergedConfig $mergedConfig
     */
    public function initMessengerAnalyser(ContainerBuilder $container, array $mergedConfig): void
    {
        $definition = $container->getDefinition(MessengerAnalyser::class);
        $definition->setArgument('$dispatchers', $mergedConfig['messenger_dispatchers']);
    }

    /**
     * @param MergedConfig $mergedConfig
     */
    public function initMethodCallFilters(ContainerBuilder $container, array $mergedConfig): void
    {
        $definition = $container->getDefinition(MethodCallCommonFilter::class);
        $definition->setArgument('$excludeLoopbackCall', $mergedConfig['filters']['method_calls']['exclude_loopback']);
        $definition->setArgument('$rules', $mergedConfig['filters']['method_calls']['rules']);
    }

    /**
     * @param MergedConfig $mergedConfig
     */
    public function initClassFilters(ContainerBuilder $container, array $mergedConfig): void
    {
        $definition = $container->getDefinition(ClassCommonFilter::class);
        $definition->setArgument('$rules', $mergedConfig['filters']['classes']['rules']);
    }

    /**
     * @param MergedConfig $mergedConfig
     */
    public function initCytoscapeGraphGenerator(ContainerBuilder $container, array $mergedConfig): void
    {
        $container->autowire(CytoscapeGraphGenerator::class, CytoscapeGraphGenerator::class);
        $definition = $container->getDefinition(CytoscapeGraphGenerator::class);
        $this->initGraphEngine($definition, $mergedConfig);
    }

    /**
     * @param MergedConfig $mergedConfig
     */
    public function initVisJsGraphGenerator(ContainerBuilder $container, array $mergedConfig): void
    {
        $container->autowire(VisJsGraphGenerator::class, VisJsGraphGenerator::class);
        $definition = $container->getDefinition(VisJsGraphGenerator::class);
        $this->initGraphEngine($definition, $mergedConfig);
    }

    /**
     * @param MergedConfig $mergedConfig
     */
    public function initPlantUmlGraphGenerator(ContainerBuilder $container, array $mergedConfig): void
    {
        $container->autowire(PlantUmlGraphGenerator::class, PlantUmlGraphGenerator::class);
        $definition = $container->getDefinition(PlantUmlGraphGenerator::class);
        $this->initGraphEngine($definition, $mergedConfig);
        $definition->setArgument('$plantUmlURI', $mergedConfig['graph']['engine_uri']);
    }

    /**
     * @param MergedConfig $mergedConfig
     */
    public function initCodebaseAnalyser(ContainerBuilder $container, array $mergedConfig): void
    {
        $definition = $container->getDefinition(CodeBaseAnalyser::class);
        $definition->setArgument('$srcRoots', $mergedConfig['sources']);
        $definition->setArgument('$cache', new Reference('symfony-cartography-cache'));
    }

    /**
     * @param MergedConfig $mergedConfig
     */
    public function initSymfonyCartographyCollector(ContainerBuilder $container, array $mergedConfig): void
    {
        $definition = $container->getDefinition(SymfonyCartographyCollector::class);
        $definition->setArgument('$enabled', $mergedConfig['enabled']);
    }

    /**
     * @param MergedConfig $mergedConfig
     */
    private function initGraphEngine(Definition $definition, array $mergedConfig): void
    {
        $definition->setArgument('$categoryColorsParameter', $mergedConfig['colors']);
        $definition->setArgument('$graphOptionsWithMethodDisplay', $mergedConfig['graph']['withMethodDisplay']);
        $definition->setArgument('$graphOptionsWithMethodArrows', $mergedConfig['graph']['withMethodArrows']);
        $definition->setArgument('$graphOptionsLeftToRightDirection', $mergedConfig['graph']['leftToRightDirection']);
    }
}
