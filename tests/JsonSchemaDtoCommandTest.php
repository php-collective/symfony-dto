<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Test;

use PhpCollective\SymfonyDto\Command\JsonSchemaDtoCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class JsonSchemaDtoCommandTest extends TestCase
{
    protected string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/symfony_dto_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
        mkdir($this->tempDir . '/config', 0777, true);
        mkdir($this->tempDir . '/schemas', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    protected function addCommandToApplication(Application $application, Command $command): void
    {
        if (method_exists($application, 'addCommand')) {
            $application->addCommand($command);
        } else {
            $application->add($command);
        }
    }

    protected function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff((array)scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testCommandFailsWhenNoConfigFound(): void
    {
        $command = new JsonSchemaDtoCommand(
            $this->tempDir,
            'config',
            'schemas',
            'Test\\Dto',
        );

        $application = new Application();
        $this->addCommandToApplication($application, $command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
        $this->assertStringContainsString('No DTO configuration files found', $commandTester->getDisplay());
    }

    public function testCommandGeneratesJsonSchema(): void
    {
        $configContent = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<dtos xmlns="php-collective-dto">
    <dto name="User">
        <field name="id" type="int"/>
        <field name="name" type="string"/>
    </dto>
</dtos>
XML;
        file_put_contents($this->tempDir . '/config/dto.xml', $configContent);

        $command = new JsonSchemaDtoCommand(
            $this->tempDir,
            'config',
            'schemas',
            'Test\\Dto',
        );

        $application = new Application();
        $this->addCommandToApplication($application, $command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertFileExists($this->tempDir . '/schemas/dto-schemas.json');
    }
}
