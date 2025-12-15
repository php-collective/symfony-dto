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

        $config = new ArrayConfig([
            'namespace' => $namespace,
            'dryRun' => $input->getOption('dry-run'),
            'verbose' => $output->isVerbose(),
        ]);

        $engine = $this->detectEngine($configPath);
        $builder = new Builder($engine, $config);
        $renderer = new TwigRenderer(null, $config);
        $consoleIo = new SymfonyConsoleIo($io);

        $generator = new Generator($builder, $renderer, $consoleIo, $config);
        $generator->generate($configPath, $outputPath);

        $io->success('DTOs generated successfully.');

        return Command::SUCCESS;
    }

    private function detectEngine(string $configPath): PhpEngine|XmlEngine|YamlEngine
    {
        if (file_exists($configPath . '/dto.php')) {
            return new PhpEngine();
        }
        if (file_exists($configPath . '/dto.xml')) {
            return new XmlEngine();
        }
        if (file_exists($configPath . '/dto.yml') || file_exists($configPath . '/dto.yaml')) {
            return new YamlEngine();
        }

        return new XmlEngine();
    }
}
