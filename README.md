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
    config_path: config          # Path to DTO config files (relative to project root)
    output_path: src/Dto         # Path for generated DTOs
    namespace: App\Dto           # Namespace for generated DTOs
```

## Usage

### 1. Create your DTO configuration

Create `config/dto.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<dtos xmlns="php-collective-dto">
    <dto name="User">
        <field name="id" type="int"/>
        <field name="name" type="string"/>
        <field name="email" type="string"/>
    </dto>
</dtos>
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

$user = new UserDto();
$user->setId(1);
$user->setName('John Doe');
$user->setEmail('john@example.com');

return $this->json($user->toArray());
```

Or create from an array:

```php
$user = UserDto::createFromArray([
    'id' => 1,
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);
```

## Supported Config Formats

The bundle supports multiple config file formats:

- `dto.xml` - XML format
- `dto.yml` / `dto.yaml` - YAML format
- `dto.php` - PHP format
- `dto/` subdirectory with multiple files

## License

MIT
