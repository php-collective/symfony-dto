<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('php_collective_dto');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('config_path')
                    ->defaultValue('config')
                    ->info('Path to DTO configuration files (relative to project root)')
                ->end()
                ->scalarNode('output_path')
                    ->defaultValue('src/Dto')
                    ->info('Path for generated DTO classes (relative to project root)')
                ->end()
                ->scalarNode('namespace')
                    ->defaultValue('App\\Dto')
                    ->info('Namespace for generated DTO classes')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
