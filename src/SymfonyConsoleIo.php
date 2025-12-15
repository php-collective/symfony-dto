<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto;

use PhpCollective\Dto\Generator\IoInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SymfonyConsoleIo implements IoInterface
{
    public function __construct(
        private SymfonyStyle $io,
    ) {
    }

    public function out(string $message): void
    {
        $this->io->text($message);
    }

    public function success(string $message): void
    {
        $this->io->success($message);
    }

    public function warning(string $message): void
    {
        $this->io->warning($message);
    }

    public function error(string $message): void
    {
        $this->io->error($message);
    }
}
