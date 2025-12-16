<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Test;

use PhpCollective\SymfonyDto\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testDefaultConfiguration(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $config = $processor->processConfiguration($configuration, []);

        $this->assertSame('config', $config['config_path']);
        $this->assertSame('src/Dto', $config['output_path']);
        $this->assertSame('App\\Dto', $config['namespace']);
    }

    public function testCustomConfiguration(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $config = $processor->processConfiguration($configuration, [
            'php_collective_dto' => [
                'config_path' => 'custom/config',
                'output_path' => 'custom/output',
                'namespace' => 'Custom\\Namespace',
            ],
        ]);

        $this->assertSame('custom/config', $config['config_path']);
        $this->assertSame('custom/output', $config['output_path']);
        $this->assertSame('Custom\\Namespace', $config['namespace']);
    }
}
