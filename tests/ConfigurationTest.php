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
        $this->assertSame('assets/types', $config['typescript_output_path']);
        $this->assertSame('config/schemas', $config['jsonschema_output_path']);
        $this->assertTrue($config['enable_value_resolver']);
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
                'typescript_output_path' => 'assets/types',
                'jsonschema_output_path' => 'config/schemas',
                'enable_value_resolver' => false,
            ],
        ]);

        $this->assertSame('custom/config', $config['config_path']);
        $this->assertSame('custom/output', $config['output_path']);
        $this->assertSame('Custom\\Namespace', $config['namespace']);
        $this->assertSame('assets/types', $config['typescript_output_path']);
        $this->assertSame('config/schemas', $config['jsonschema_output_path']);
        $this->assertFalse($config['enable_value_resolver']);
    }
}
