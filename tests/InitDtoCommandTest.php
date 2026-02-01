<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Test;

use PhpCollective\SymfonyDto\Command\InitDtoCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class InitDtoCommandTest extends TestCase
{
    protected string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/symfony_dto_init_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
        mkdir($this->tempDir . '/config', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
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

    public function testCommandName(): void
    {
        $command = new InitDtoCommand($this->tempDir, 'config');

        $this->assertSame('dto:init', $command->getName());
    }

    public function testInitCreatesPhpConfig(): void
    {
        $command = new InitDtoCommand($this->tempDir, 'config');

        $application = new Application();
        $application->addCommand($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertFileExists($this->tempDir . '/config/dtos.php');

        $content = file_get_contents($this->tempDir . '/config/dtos.php');
        $this->assertStringContainsString('return [', $content);
        $this->assertStringContainsString("'name' => 'User'", $content);
    }

    public function testInitCreatesXmlConfig(): void
    {
        $command = new InitDtoCommand($this->tempDir, 'config');

        $application = new Application();
        $application->addCommand($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['--format' => 'xml']);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertFileExists($this->tempDir . '/config/dto.xml');

        $content = file_get_contents($this->tempDir . '/config/dto.xml');
        $this->assertStringContainsString('<?xml version="1.0"', $content);
        $this->assertStringContainsString('<dto name="User">', $content);
    }

    public function testInitCreatesYamlConfig(): void
    {
        $command = new InitDtoCommand($this->tempDir, 'config');

        $application = new Application();
        $application->addCommand($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['--format' => 'yaml']);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $this->assertFileExists($this->tempDir . '/config/dto.yaml');

        $content = file_get_contents($this->tempDir . '/config/dto.yaml');
        $this->assertStringContainsString('User:', $content);
        $this->assertStringContainsString('fields:', $content);
    }

    public function testInitFailsWhenFileExists(): void
    {
        file_put_contents($this->tempDir . '/config/dtos.php', '<?php return [];');

        $command = new InitDtoCommand($this->tempDir, 'config');

        $application = new Application();
        $application->addCommand($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
        $this->assertStringContainsString('Config file already exists', $commandTester->getDisplay());
    }

    public function testInitOverwritesWithForce(): void
    {
        file_put_contents($this->tempDir . '/config/dtos.php', '<?php return [];');

        $command = new InitDtoCommand($this->tempDir, 'config');

        $application = new Application();
        $application->addCommand($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['--force' => true]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $content = file_get_contents($this->tempDir . '/config/dtos.php');
        $this->assertStringContainsString("'name' => 'User'", $content);
    }

    public function testInitFailsWithInvalidFormat(): void
    {
        $command = new InitDtoCommand($this->tempDir, 'config');

        $application = new Application();
        $application->addCommand($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['--format' => 'invalid']);

        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
        $this->assertStringContainsString('Invalid format', $commandTester->getDisplay());
    }
}
