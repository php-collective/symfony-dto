<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto;

use PhpCollective\Dto\Generator\IoInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SymfonyConsoleIo implements IoInterface
{
    public function __construct(private SymfonyStyle $io)
    {
    }

    /**
     * @inheritDoc
     */
    public function verbose(array|string $message, int $newlines = 1): ?int
    {
        if (is_array($message)) {
            $message = implode(PHP_EOL, $message);
        }
        $this->io->text($message);

        return null;
    }

    /**
     * @inheritDoc
     */
    public function quiet(array|string $message, int $newlines = 1): ?int
    {
        if (is_array($message)) {
            $message = implode(PHP_EOL, $message);
        }
        $this->io->text($message);

        return null;
    }

    /**
     * @inheritDoc
     */
    public function out(?string $message = null, int $newlines = 1, int $level = self::NORMAL): ?int
    {
        if ($message === null) {
            return null;
        }
        $this->io->text($message);

        return null;
    }

    /**
     * @inheritDoc
     */
    public function error(?string $message = null, int $newlines = 1): ?int
    {
        if ($message === null) {
            return null;
        }
        $this->io->error($message);

        return null;
    }

    /**
     * @inheritDoc
     */
    public function success(?string $message = null, int $newlines = 1, int $level = self::NORMAL): ?int
    {
        if ($message === null) {
            return null;
        }
        $this->io->success($message);

        return null;
    }

    /**
     * @inheritDoc
     */
    public function abort(string $message, int $exitCode = 1): void
    {
        $this->io->error($message);
        exit($exitCode);
    }
}
