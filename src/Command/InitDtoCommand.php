<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'dto:init',
    description: 'Create a starter DTO configuration file',
)]
class InitDtoCommand extends Command
{
    public function __construct(
        private string $projectDir,
        private string $configPath = 'config',
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Config format (php, xml, yaml)', 'php')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing config file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $format = strtolower((string)$input->getOption('format'));
        $configPath = $this->projectDir . '/' . $this->configPath;

        if (!in_array($format, ['php', 'xml', 'yaml', 'yml'], true)) {
            $io->error("Invalid format: {$format}. Use php, xml, or yaml.");

            return Command::FAILURE;
        }

        if ($format === 'yml') {
            $format = 'yaml';
        }

        $filename = $format === 'php' ? 'dtos.php' : 'dto.' . $format;
        $filePath = rtrim($configPath, '/') . '/' . $filename;

        if (file_exists($filePath) && !$input->getOption('force')) {
            $io->error("Config file already exists: {$filePath}");
            $io->writeln('Use --force to overwrite.');

            return Command::FAILURE;
        }

        $content = $this->getConfigContent($format);

        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        file_put_contents($filePath, $content);

        $io->success("Created DTO configuration: {$filePath}");
        $io->writeln('');
        $io->writeln('Next steps:');
        $io->writeln('  1. Edit the configuration to define your DTOs');
        $io->writeln('  2. Run "bin/console dto:generate" to generate the DTO classes');

        return Command::SUCCESS;
    }

    private function getConfigContent(string $format): string
    {
        return match ($format) {
            'php' => $this->getPhpConfig(),
            'xml' => $this->getXmlConfig(),
            default => $this->getYamlConfig(),
        };
    }

    private function getPhpConfig(): string
    {
        return <<<'PHP'
<?php

/**
 * DTO Configuration
 *
 * Define your Data Transfer Objects here.
 *
 * @see https://github.com/php-collective/dto
 */
return [
    // Example DTO definition
    [
        'name' => 'User',
        'fields' => [
            [
                'name' => 'id',
                'type' => 'int',
            ],
            [
                'name' => 'email',
                'type' => 'string',
            ],
            [
                'name' => 'name',
                'type' => 'string',
                'nullable' => true,
            ],
            [
                'name' => 'createdAt',
                'type' => '\DateTimeInterface',
                'nullable' => true,
            ],
        ],
    ],

    // Add more DTOs here...
];
PHP;
    }

    private function getXmlConfig(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<!--
    DTO Configuration

    Define your Data Transfer Objects here.

    @see https://github.com/php-collective/dto
-->
<dtos xmlns="php-collective-dto">
    <!-- Example DTO definition -->
    <dto name="User">
        <field name="id" type="int"/>
        <field name="email" type="string"/>
        <field name="name" type="string" nullable="true"/>
        <field name="createdAt" type="\DateTimeInterface" nullable="true"/>
    </dto>

    <!-- Add more DTOs here... -->
</dtos>
XML;
    }

    private function getYamlConfig(): string
    {
        return <<<'YAML'
# DTO Configuration
#
# Define your Data Transfer Objects here.
#
# @see https://github.com/php-collective/dto

# Example DTO definition
User:
  fields:
    id:
      type: int
    email:
      type: string
    name:
      type: string
      nullable: true
    createdAt:
      type: \DateTimeInterface
      nullable: true

# Add more DTOs here...
YAML;
    }
}
