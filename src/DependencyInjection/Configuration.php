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
                ->scalarNode('typescript_output_path')
                    ->defaultValue('assets/types')
                    ->info('Path for generated TypeScript interfaces (relative to project root)')
                ->end()
                ->scalarNode('jsonschema_output_path')
                    ->defaultValue('config/schemas')
                    ->info('Path for generated JSON Schema files (relative to project root)')
                ->end()
                ->booleanNode('enable_value_resolver')
                    ->defaultTrue()
                    ->info('Enable automatic DTO value resolver for controller arguments')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
