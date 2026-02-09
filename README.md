# Symfony DTO Bundle

Symfony bundle integration for [php-collective/dto](https://github.com/php-collective/dto).

## Installation

```bash
composer require php-collective/symfony-dto
```

The bundle will be auto-configured if you're using Symfony Flex.

### Manual Registration

If not using Flex, add to `config/bundles.php`:

```php
return [
    // ...
    PhpCollective\SymfonyDto\PhpCollectiveDtoBundle::class => ['all' => true],
];
```

## Configuration

Create `config/packages/php_collective_dto.yaml`:

```yaml
php_collective_dto:
    config_path: config/          # Path to DTO config files (relative to project root)
    output_path: src/Dto/        # Path for generated DTOs
    namespace: App\Dto           # Namespace for generated DTOs
    typescript_output_path: assets/types  # TypeScript output
    jsonschema_output_path: config/schemas  # JSON Schema output
    enable_value_resolver: true  # Enable controller DTO auto-resolution
```

## Usage

### 1. Initialize DTO configuration

```bash
bin/console dto:init
```

This creates a `config/dtos.php` file with a sample DTO definition (PHP format is the default).
You can also use `--format=xml` or `--format=yaml`.

The generated config looks like:

```php
use PhpCollective\Dto\Builder\Dto;
use PhpCollective\Dto\Builder\Field;
use PhpCollective\Dto\Builder\Schema;

return Schema::create()
    ->dto(Dto::create('User')->fields(
        Field::int('id'),
        Field::string('name'),
        Field::string('email')->nullable(),
    ))
    ->toArray();
```

### 2. Generate DTOs

```bash
bin/console dto:generate
```

Options:
- `--dry-run` - Preview changes without writing files
- `--config-path` - Override config path
- `--output-path` - Override output path
- `--namespace` - Override namespace
- `-v` - Verbose output

### 3. Generate TypeScript interfaces

```bash
bin/console dto:typescript
bin/console dto:typescript --multiple-files --readonly
```

### 4. Generate JSON Schema

```bash
bin/console dto:jsonschema
bin/console dto:jsonschema --multiple-files
```

### 5. Use your DTOs

```php
use App\Dto\UserDto;

$user = new UserDto([
    'id' => 1,
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);

return $this->json($user->toArray());
```

## DTO Mapping Helpers

```php
use App\Dto\UserDto;
use PhpCollective\SymfonyDto\Mapper\DtoMapper;

$dto = DtoMapper::fromArray(['name' => 'Mark'], UserDto::class);

$dtos = DtoMapper::fromIterable($rows, UserDto::class);
$collection = DtoMapper::fromCollection($doctrineCollection, UserDto::class);

// Generic pagination wrapper
$pagination = DtoMapper::fromPaginated(
    items: $pageItems,
    total: $total,
    perPage: $perPage,
    page: $page,
    dtoClass: UserDto::class,
);
```

## JSON Response Helper

```php
use PhpCollective\SymfonyDto\Http\DtoJsonResponse;

return DtoJsonResponse::fromDto($dto);
// or
return DtoJsonResponse::fromCollection($dtos);
```

## Controller DTO Resolution

When `enable_value_resolver` is enabled, you can use `#[MapRequestDto]` to map request data to DTOs:

```php
use PhpCollective\SymfonyDto\Attribute\MapRequestDto;

#[Route('/users', methods: ['POST'])]
public function create(#[MapRequestDto] UserDto $dto): Response
{
    // $dto is built from request data
}
```

The `source` option controls where data comes from: `body`, `query`, `request`, or `auto`.

## Collections

The bundle automatically registers Doctrine's `ArrayCollection` for DTO collection fields. Define collection fields with the `[]` suffix:

```php
Field::array('roles', 'Role'),  // Role[] collection
Field::array('tags', 'string'), // string[] collection
```

After generating, collection fields use Doctrine's `ArrayCollection` class with its methods (`filter`, `map`, `first`, etc.).

## Validation Bridge

Automatically convert DTO validation rules to Symfony Validator constraints:

```php
use PhpCollective\SymfonyDto\Validation\DtoConstraintBuilder;
use Symfony\Component\Validator\Validation;

$constraint = DtoConstraintBuilder::fromDto(new UserDto());
$violations = Validation::createValidator()->validate($data, $constraint);
```

See [Usage docs](docs/README.md#validation-bridge) for details.

## Supported Config Formats

The bundle supports multiple config file formats:

- `dtos.php` - PHP format (default)
- `dto.xml` - XML format
- `dto.yml` / `dto.yaml` - YAML format
- `dto/` subdirectory with multiple files

## License

MIT
