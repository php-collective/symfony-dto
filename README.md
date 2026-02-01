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

### 3. Use your DTOs

```php
use App\Dto\UserDto;

$user = new UserDto([
    'id' => 1,
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);

return $this->json($user->toArray());
```

## Collections

The bundle automatically registers Doctrine's `ArrayCollection` for DTO collection fields. Define collection fields with the `[]` suffix:

```php
Field::array('roles', 'Role'),  // Role[] collection
Field::array('tags', 'string'), // string[] collection
```

After generating, collection fields use Doctrine's `ArrayCollection` class with its methods (`filter`, `map`, `first`, etc.).

## Supported Config Formats

The bundle supports multiple config file formats:

- `dtos.php` - PHP format (default)
- `dto.xml` - XML format
- `dto.yml` / `dto.yaml` - YAML format
- `dto/` subdirectory with multiple files

## License

MIT
