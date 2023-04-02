<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('symfonycartographybundle');

        /**
         * @psalm-suppress PossiblyUndefinedMethod
         * @psalm-suppress MixedMethodCall
         * @psalm-suppress UndefinedMethod
         */
        $treeBuilder
            ->getRootNode()
                ->children()
                    ->booleanNode('enabled')->defaultTrue()->end()
                    ->arrayNode('graph')
                        ->children()
                            ->booleanNode('withMethodDisplay')->defaultFalse()->end()
                            ->booleanNode('withMethodArrows')->defaultFalse()->end()
                            ->booleanNode('leftToRightDirection')->defaultFalse()->end()
                        ->end()
                    ->end()
                    ->arrayNode('sources')
                        ->scalarPrototype()->defaultValue(['%kernel.project_dir%/src'])->end()
                    ->end()
                    ->arrayNode('filters')
                        ->children()
                            ->arrayNode('classes')
                                ->children()
                                    ->arrayNode('rules')
                                    ->scalarPrototype()->defaultValue([])->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('method_calls')
                            ->children()
                                ->booleanNode('exclude_loopback')->defaultFalse()->end()
                                ->arrayNode('rules')
                                    ->scalarPrototype()->defaultValue([])->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('colors')
                    ->arrayPrototype()
                        ->children()
                            ->variableNode('class')->end()
                            ->scalarNode('color')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('messenger_dispatchers')
                    ->arrayPrototype()
                        ->children()
                            ->variableNode('class')->end()
                            ->scalarNode('method')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
