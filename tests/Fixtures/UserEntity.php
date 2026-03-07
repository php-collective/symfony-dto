<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Test\Fixtures;

/**
 * Simple entity fixture for testing AutoMapper bridge.
 */
class UserEntity
{
    private int $id;

    private string $name;

    private string $email;

    private bool $active = true;

    public function __construct(int $id = 0, string $name = '', string $email = '')
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}
