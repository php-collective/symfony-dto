<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Command;

use PhpCollective\Dto\Engine\PhpEngine;
use PhpCollective\Dto\Engine\XmlEngine;
use PhpCollective\Dto\Engine\YamlEngine;
use PhpCollective\Dto\Generator\ArrayConfig;
use PhpCollective\Dto\Generator\Builder;
use PhpCollective\Dto\Generator\JsonSchemaGenerator;
use PhpCollective\SymfonyDto\SymfonyConsoleIo;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'dto:jsonschema',
    description: 'Generate JSON Schema from DTO configuration',
)]
class JsonSchemaDtoCommand extends Command
{
    public function __construct(
        private string $projectDir,
        private string $configPath = 'config',
        private string $outputPath = 'config/schemas',
        private string $namespace = 'App\\Dto',
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('config-path', null, InputOption::VALUE_REQUIRED, 'Path to DTO config files')
            ->addOption('output-path', null, InputOption::VALUE_REQUIRED, 'Path for generated JSON Schema files')
            ->addOption('namespace', null, InputOption::VALUE_REQUIRED, 'Namespace for DTOs')
            ->addOption('single-file', null, InputOption::VALUE_NONE, 'Generate all schemas in a single file with $defs (default)')
            ->addOption('multiple-files', null, InputOption::VALUE_NONE, 'Generate each schema in its own file')
            ->addOption('schema-version', null, InputOption::VALUE_REQUIRED, 'JSON Schema version', 'https://json-schema.org/draft/2020-12/schema')
            ->addOption('date-format', null, InputOption::VALUE_REQUIRED, 'Date format: date-time, date, or string', 'date-time')
            ->addOption('no-refs', null, InputOption::VALUE_NONE, 'Inline nested schemas instead of using $ref')
            ->addOption('suffix', null, InputOption::VALUE_REQUIRED, 'Suffix for schema names', 'Dto');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $configPath = $input->getOption('config-path') ?? $this->projectDir . '/' . $this->configPath;
        $outputPath = $input->getOption('output-path') ?? $this->projectDir . '/' . $this->outputPath;
        $namespace = $input->getOption('namespace') ?? $this->namespace;

        // Ensure paths end with /
        if (!str_ends_with($configPath, '/')) {
            $configPath .= '/';
        }
        if (!str_ends_with($outputPath, '/')) {
            $outputPath .= '/';
        }

        // Validate config path exists
        if (!is_dir($configPath)) {
            $io->error("Config path does not exist: {$configPath}");

            return Command::FAILURE;
        }

        // Check if any config files exist
        $engine = $this->detectEngine($configPath);
        if ($engine === null) {
            $io->error("No DTO configuration files found in: {$configPath}");
            $io->writeln('');
            $io->writeln('Expected one of:');
            $io->writeln('  - dtos.php, dtos.xml, dtos.yml, dtos.yaml');
            $io->writeln('  - dto.php, dto.xml, dto.yml, dto.yaml');
            $io->writeln('  - dto/ subdirectory with config files');
            $io->writeln('');
            $io->writeln('Run "bin/console dto:init" to create a starter configuration.');

            return Command::FAILURE;
        }

        $config = new ArrayConfig([
            'namespace' => $namespace,
        ]);

        $builder = new Builder($engine, $config);
        $definitions = $builder->build($configPath, [
            'namespace' => str_replace('\\Dto', '', $namespace),
        ]);

        // Transform definitions to the format expected by JsonSchemaGenerator
        $transformedDefinitions = $this->transformDefinitions($definitions);

        $consoleIo = new SymfonyConsoleIo($io);

        $options = [
            'singleFile' => !$input->getOption('multiple-files'),
            'schemaVersion' => (string)$input->getOption('schema-version'),
            'dateFormat' => (string)$input->getOption('date-format'),
            'useRefs' => !$input->getOption('no-refs'),
            'suffix' => (string)$input->getOption('suffix'),
        ];

        $generator = new JsonSchemaGenerator($consoleIo, $options);
        $count = $generator->generate($transformedDefinitions, $outputPath);

        $io->success("Generated {$count} JSON Schema file(s) in {$outputPath}");

        return Command::SUCCESS;
    }

    /**
     * Transform Builder definitions to the format expected by JsonSchemaGenerator.
     *
     * @param array<string, array<string, mixed>> $definitions
     *
     * @return array<string, array<string, mixed>>
     */
    private function transformDefinitions(array $definitions): array
    {
        $result = [];

        foreach ($definitions as $name => $dto) {
            $fields = [];

            foreach ($dto['fields'] ?? [] as $fieldName => $field) {
                $fields[$fieldName] = [
                    'type' => $field['type'] ?? 'mixed',
                    'required' => $field['required'] ?? false,
                    'nullable' => $field['nullable'] ?? true,
                    'collection' => $field['collection'] ?? false,
                    'isArray' => $field['isArray'] ?? false,
                    'singular' => $field['singular'] ?? null,
                    'singularType' => $field['singularType'] ?? null,
                    'singularClass' => $field['singularClass'] ?? null,
                    'dto' => $field['dto'] ?? null,
                ];
            }

            $result[$name] = [
                'fields' => $fields,
                'immutable' => $dto['immutable'] ?? false,
                'description' => $dto['deprecated'] ?? null,
            ];
        }

        return $result;
    }

    private function detectEngine(string $configPath): PhpEngine|XmlEngine|YamlEngine|null
    {
        $sep = str_ends_with($configPath, '/') ? '' : '/';

        // Check for dtos.* files first (alternative naming)
        if (file_exists($configPath . $sep . 'dtos.php')) {
            return new PhpEngine();
        }
        if (file_exists($configPath . $sep . 'dtos.xml')) {
            return new XmlEngine();
        }
        if (file_exists($configPath . $sep . 'dtos.yml') || file_exists($configPath . $sep . 'dtos.yaml')) {
            return new YamlEngine();
        }

        // Standard dto.* naming
        if (file_exists($configPath . $sep . 'dto.php')) {
            return new PhpEngine();
        }
        if (file_exists($configPath . $sep . 'dto.xml')) {
            return new XmlEngine();
        }
        if (file_exists($configPath . $sep . 'dto.yml') || file_exists($configPath . $sep . 'dto.yaml')) {
            return new YamlEngine();
        }

        // Check for dto/ subdirectory
        if (is_dir($configPath . $sep . 'dto')) {
            $dtoDir = $configPath . $sep . 'dto/';
            if (glob($dtoDir . '*.php')) {
                return new PhpEngine();
            }
            if (glob($dtoDir . '*.xml')) {
                return new XmlEngine();
            }
            if (glob($dtoDir . '*.yml') || glob($dtoDir . '*.yaml')) {
                return new YamlEngine();
            }
        }

        return null;
    }
}
