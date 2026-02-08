<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Test\Fixtures;

use PhpCollective\Dto\Dto\AbstractDto;

/**
 * A test DTO with validation metadata for testing the constraint bridge.
 */
class ValidationTestDto extends AbstractDto
{
    /**
     * @var string
     */
    public const FIELD_NAME = 'name';

    /**
     * @var string
     */
    public const FIELD_EMAIL = 'email';

    /**
     * @var string
     */
    public const FIELD_AGE = 'age';

    protected ?string $name = null;

    protected ?string $email = null;

    protected ?int $age = null;

    /**
     * @param array<string, mixed> $data
     * @param bool $ignoreMissing
     */
    public function __construct(array $data = [], bool $ignoreMissing = true)
    {
        parent::__construct($data, $ignoreMissing);
    }

    /**
     * Skip validation in test fixture — we just need metadata access.
     *
     * @return void
     */
    protected function validate(): void
    {
    }

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $_metadata = [
        'name' => [
            'type' => 'string',
            'required' => true,
            'defaultValue' => null,
            'dto' => false,
            'collectionType' => null,
            'singularType' => null,
            'associative' => false,
            'key' => null,
            'serialize' => null,
            'factory' => null,
            'isClass' => false,
            'enum' => null,
            'minLength' => 2,
            'maxLength' => 50,
            'min' => null,
            'max' => null,
            'pattern' => null,
        ],
        'email' => [
            'type' => 'string',
            'required' => false,
            'defaultValue' => null,
            'dto' => false,
            'collectionType' => null,
            'singularType' => null,
            'associative' => false,
            'key' => null,
            'serialize' => null,
            'factory' => null,
            'isClass' => false,
            'enum' => null,
            'minLength' => null,
            'maxLength' => null,
            'min' => null,
            'max' => null,
            'pattern' => '/^[^@]+@[^@]+\.[^@]+$/',
        ],
        'age' => [
            'type' => 'int',
            'required' => false,
            'defaultValue' => null,
            'dto' => false,
            'collectionType' => null,
            'singularType' => null,
            'associative' => false,
            'key' => null,
            'serialize' => null,
            'factory' => null,
            'isClass' => false,
            'enum' => null,
            'minLength' => null,
            'maxLength' => null,
            'min' => 0,
            'max' => 150,
            'pattern' => null,
        ],
    ];

    /**
     * @return array<string, array<string, mixed>>
     */
    public function validationRules(): array
    {
        $rules = [];
        foreach ($this->_metadata as $field => $meta) {
            $rule = [];
            if ($meta['required']) {
                $rule['required'] = true;
            }
            if ($meta['minLength'] !== null) {
                $rule['minLength'] = $meta['minLength'];
            }
            if ($meta['maxLength'] !== null) {
                $rule['maxLength'] = $meta['maxLength'];
            }
            if ($meta['min'] !== null) {
                $rule['min'] = $meta['min'];
            }
            if ($meta['max'] !== null) {
                $rule['max'] = $meta['max'];
            }
            if ($meta['pattern'] !== null) {
                $rule['pattern'] = $meta['pattern'];
            }
            if ($rule) {
                $rules[$field] = $rule;
            }
        }

        return $rules;
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
        $instance = new static();
        if (isset($data['name'])) {
            $instance->name = $data['name'];
        }
        if (isset($data['email'])) {
            $instance->email = $data['email'];
        }
        if (isset($data['age'])) {
            $instance->age = $data['age'];
        }

        return $instance;
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
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'age' => $this->age,
        ], fn ($v) => $v !== null);
    }
}
