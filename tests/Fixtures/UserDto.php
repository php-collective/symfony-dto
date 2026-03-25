<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Test\Fixtures;

use PhpCollective\Dto\Dto\AbstractDto;

/**
 * Simple DTO fixture for testing AutoMapper bridge.
 */
class UserDto extends AbstractDto
{
    private ?int $id = null;

    private ?string $name = null;

    private ?string $email = null;

    private bool $active = true;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        if (isset($data['id'])) {
            $this->id = (int)$data['id'];
        }
        if (isset($data['name'])) {
            $this->name = (string)$data['name'];
        }
        if (isset($data['email'])) {
            $this->email = (string)$data['email'];
        }
        if (isset($data['active'])) {
            $this->active = (bool)$data['active'];
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param bool $ignoreMissing
     * @param string|null $type
     *
     * @return static
     */
    public static function createFromArray(array $data, bool $ignoreMissing = false, ?string $type = null): static
    {
        return new static($data);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
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

    /**
     * @param string|null $type
     * @param array<string>|null $fields
     * @param bool $touched
     *
     * @return array<string, mixed>
     */
    public function toArray(?string $type = null, ?array $fields = null, bool $touched = false): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'active' => $this->active,
        ];
    }
}
