<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\DependencyInjection;

use PhpCollective\SymfonyDto\Command\GenerateDtoCommand;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;

class PhpCollectiveDtoExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $definition = new Definition(GenerateDtoCommand::class);
        $definition->setArgument('$projectDir', '%kernel.project_dir%');
        $definition->setArgument('$configPath', $config['config_path']);
        $definition->setArgument('$outputPath', $config['output_path']);
        $definition->setArgument('$namespace', $config['namespace']);
        $definition->addTag('console.command');

        $container->setDefinition(GenerateDtoCommand::class, $definition);
    }
}
