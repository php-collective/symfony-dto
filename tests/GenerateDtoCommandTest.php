<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Test;

use PhpCollective\SymfonyDto\Command\GenerateDtoCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateDtoCommandTest extends TestCase
{
    protected string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/symfony_dto_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
        mkdir($this->tempDir . '/config', 0777, true);
        mkdir($this->tempDir . '/output', 0777, true);
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
        $command = new GenerateDtoCommand(
            $this->tempDir,
            'config',
            'output',
            'Test\\Dto',
        );

        $this->assertSame('dto:generate', $command->getName());
    }

    public function testCommandFailsWhenNoConfigFound(): void
    {
        $command = new GenerateDtoCommand(
            $this->tempDir,
            'config',
            'output',
            'Test\\Dto',
        );

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
        $this->assertStringContainsString('No DTO configuration files found', $commandTester->getDisplay());
    }

    public function testCommandWithDryRun(): void
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

        $command = new GenerateDtoCommand(
            $this->tempDir,
            'config',
            'output',
            'Test\\Dto',
        );

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['--dry-run' => true]);

        $this->assertSame(0, $commandTester->getStatusCode());

        // With dry-run, no files should be created
        $this->assertFileDoesNotExist($this->tempDir . '/output/Dto/UserDto.php');
    }

    public function testCommandGeneratesDto(): void
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

        $command = new GenerateDtoCommand(
            $this->tempDir,
            'config',
            'output',
            'Test\\Dto',
        );

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertFileExists($this->tempDir . '/output/Dto/UserDto.php');

        $content = file_get_contents($this->tempDir . '/output/Dto/UserDto.php');
        $this->assertStringContainsString('namespace Test\\Dto', $content);
        $this->assertStringContainsString('class UserDto', $content);
    }

    public function testCommandWithCustomPaths(): void
    {
        $configContent = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<dtos xmlns="php-collective-dto">
    <dto name="Product">
        <field name="id" type="int"/>
    </dto>
</dtos>
XML;
        file_put_contents($this->tempDir . '/config/dto.xml', $configContent);

        $command = new GenerateDtoCommand(
            $this->tempDir,
            'config',
            'output',
            'Test\\Dto',
        );

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--config-path' => $this->tempDir . '/config',
            '--output-path' => $this->tempDir . '/output',
            '--namespace' => 'Custom\\Dto',
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertFileExists($this->tempDir . '/output/Dto/ProductDto.php');

        $content = file_get_contents($this->tempDir . '/output/Dto/ProductDto.php');
        $this->assertStringContainsString('namespace Custom\\Dto', $content);
    }
}
