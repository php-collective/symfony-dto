<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\DependencyInjection;

use PhpCollective\SymfonyDto\Command\GenerateDtoCommand;
use PhpCollective\SymfonyDto\Command\InitDtoCommand;
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
    }
}
