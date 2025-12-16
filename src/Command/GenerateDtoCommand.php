<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Command;

use PhpCollective\Dto\Engine\PhpEngine;
use PhpCollective\Dto\Engine\XmlEngine;
use PhpCollective\Dto\Engine\YamlEngine;
use PhpCollective\Dto\Generator\ArrayConfig;
use PhpCollective\Dto\Generator\Builder;
use PhpCollective\Dto\Generator\Generator;
use PhpCollective\Dto\Generator\TwigRenderer;
use PhpCollective\SymfonyDto\SymfonyConsoleIo;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'dto:generate',
    description: 'Generate DTO classes from configuration',
)]
class GenerateDtoCommand extends Command
{
    public function __construct(
        private string $projectDir,
        private string $configPath = 'config',
        private string $outputPath = 'src/Dto',
        private string $namespace = 'App\\Dto',
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Preview changes without writing files')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force regeneration of all DTOs')
            ->addOption('config-path', null, InputOption::VALUE_REQUIRED, 'Path to DTO config files')
            ->addOption('output-path', null, InputOption::VALUE_REQUIRED, 'Path for generated DTOs')
            ->addOption('namespace', null, InputOption::VALUE_REQUIRED, 'Namespace for generated DTOs');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $configPath = $input->getOption('config-path') ?? $this->projectDir . '/' . $this->configPath;
        $outputPath = $input->getOption('output-path') ?? $this->projectDir . '/' . $this->outputPath;
        $namespace = $input->getOption('namespace') ?? $this->namespace;

        // Ensure paths end with / for the Finder class
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
            'dryRun' => $input->getOption('dry-run'),
            'verbose' => $output->isVerbose(),
        ]);

        $builder = new Builder($engine, $config);
        $renderer = new TwigRenderer(null, $config);
        $consoleIo = new SymfonyConsoleIo($io);

        $generator = new Generator($builder, $renderer, $consoleIo, $config);
        $generator->generate($configPath, $outputPath, [
            'dryRun' => $input->getOption('dry-run'),
            'verbose' => $output->isVerbose(),
            'force' => $input->getOption('force'),
        ]);

        if ($input->getOption('dry-run')) {
            $io->success('Dry run complete. No files were written.');
        } else {
            $io->success('DTOs generated successfully.');
        }

        return Command::SUCCESS;
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
