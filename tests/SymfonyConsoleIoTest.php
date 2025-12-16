<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Test;

use PhpCollective\Dto\Generator\IoInterface;
use PhpCollective\SymfonyDto\SymfonyConsoleIo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;

class SymfonyConsoleIoTest extends TestCase
{
    public function testImplementsIoInterface(): void
    {
        $style = $this->createMock(SymfonyStyle::class);
        $io = new SymfonyConsoleIo($style);

        $this->assertInstanceOf(IoInterface::class, $io);
    }

    public function testVerboseWithString(): void
    {
        $style = $this->createMock(SymfonyStyle::class);
        $style->expects($this->once())
            ->method('text')
            ->with('test message');

        $io = new SymfonyConsoleIo($style);
        $result = $io->verbose('test message');

        $this->assertNull($result);
    }

    public function testVerboseWithArray(): void
    {
        $style = $this->createMock(SymfonyStyle::class);
        $style->expects($this->once())
            ->method('text')
            ->with("line1\nline2");

        $io = new SymfonyConsoleIo($style);
        $io->verbose(['line1', 'line2']);
    }

    public function testQuietWithString(): void
    {
        $style = $this->createMock(SymfonyStyle::class);
        $style->expects($this->once())
            ->method('text')
            ->with('quiet message');

        $io = new SymfonyConsoleIo($style);
        $result = $io->quiet('quiet message');

        $this->assertNull($result);
    }

    public function testOutWithMessage(): void
    {
        $style = $this->createMock(SymfonyStyle::class);
        $style->expects($this->once())
            ->method('text')
            ->with('output message');

        $io = new SymfonyConsoleIo($style);
        $result = $io->out('output message');

        $this->assertNull($result);
    }

    public function testOutWithNull(): void
    {
        $style = $this->createMock(SymfonyStyle::class);
        $style->expects($this->never())
            ->method('text');

        $io = new SymfonyConsoleIo($style);
        $result = $io->out(null);

        $this->assertNull($result);
    }

    public function testErrorWithMessage(): void
    {
        $style = $this->createMock(SymfonyStyle::class);
        $style->expects($this->once())
            ->method('error')
            ->with('error message');

        $io = new SymfonyConsoleIo($style);
        $result = $io->error('error message');

        $this->assertNull($result);
    }

    public function testErrorWithNull(): void
    {
        $style = $this->createMock(SymfonyStyle::class);
        $style->expects($this->never())
            ->method('error');

        $io = new SymfonyConsoleIo($style);
        $result = $io->error(null);

        $this->assertNull($result);
    }

    public function testSuccessWithMessage(): void
    {
        $style = $this->createMock(SymfonyStyle::class);
        $style->expects($this->once())
            ->method('success')
            ->with('success message');

        $io = new SymfonyConsoleIo($style);
        $result = $io->success('success message');

        $this->assertNull($result);
    }

    public function testSuccessWithNull(): void
    {
        $style = $this->createMock(SymfonyStyle::class);
        $style->expects($this->never())
            ->method('success');

        $io = new SymfonyConsoleIo($style);
        $result = $io->success(null);

        $this->assertNull($result);
    }
}
