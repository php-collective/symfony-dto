<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\DependencyInjection;

use PhpCollective\SymfonyDto\Command\GenerateDtoCommand;
use PhpCollective\SymfonyDto\Command\InitDtoCommand;
use PhpCollective\SymfonyDto\Command\JsonSchemaDtoCommand;
use PhpCollective\SymfonyDto\Command\TypeScriptDtoCommand;
use PhpCollective\SymfonyDto\Http\DtoValueResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;

class PhpCollectiveDtoExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Register dto:generate command
        $generateDefinition = new Definition(GenerateDtoCommand::class);
        $generateDefinition->setArgument('$projectDir', '%kernel.project_dir%');
        $generateDefinition->setArgument('$configPath', $config['config_path']);
        $generateDefinition->setArgument('$outputPath', $config['output_path']);
        $generateDefinition->setArgument('$namespace', $config['namespace']);
        $generateDefinition->addTag('console.command');
        $container->setDefinition(GenerateDtoCommand::class, $generateDefinition);

        // Register dto:init command
        $initDefinition = new Definition(InitDtoCommand::class);
        $initDefinition->setArgument('$projectDir', '%kernel.project_dir%');
        $initDefinition->setArgument('$configPath', $config['config_path']);
        $initDefinition->addTag('console.command');
        $container->setDefinition(InitDtoCommand::class, $initDefinition);

        // Register dto:typescript command
        $typescriptDefinition = new Definition(TypeScriptDtoCommand::class);
        $typescriptDefinition->setArgument('$projectDir', '%kernel.project_dir%');
        $typescriptDefinition->setArgument('$configPath', $config['config_path']);
        $typescriptDefinition->setArgument('$outputPath', $config['typescript_output_path']);
        $typescriptDefinition->setArgument('$namespace', $config['namespace']);
        $typescriptDefinition->addTag('console.command');
        $container->setDefinition(TypeScriptDtoCommand::class, $typescriptDefinition);

        // Register dto:jsonschema command
        $jsonschemaDefinition = new Definition(JsonSchemaDtoCommand::class);
        $jsonschemaDefinition->setArgument('$projectDir', '%kernel.project_dir%');
        $jsonschemaDefinition->setArgument('$configPath', $config['config_path']);
        $jsonschemaDefinition->setArgument('$outputPath', $config['jsonschema_output_path']);
        $jsonschemaDefinition->setArgument('$namespace', $config['namespace']);
        $jsonschemaDefinition->addTag('console.command');
        $container->setDefinition(JsonSchemaDtoCommand::class, $jsonschemaDefinition);

        // Register DTO value resolver
        if ($config['enable_value_resolver']) {
            $resolverDefinition = new Definition(DtoValueResolver::class);
            $resolverDefinition->addTag('controller.argument_value_resolver', ['priority' => 50]);
            $container->setDefinition(DtoValueResolver::class, $resolverDefinition);
        }
    }
}
