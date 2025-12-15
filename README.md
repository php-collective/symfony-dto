# Symfony DTO Bundle

Symfony integration for [php-collective/dto](https://github.com/php-collective/dto).

## Installation

```bash
composer require php-collective/symfony-dto
```

Register the bundle in `config/bundles.php`:

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
    config_path: 'config/'      # Where DTO config files are located
    output_path: 'src/'         # Where to generate DTOs
    namespace: 'App'            # Namespace for generated DTOs
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
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController
{
    public function create(): JsonResponse
    {
        $user = new UserDto();
        $user->setId(1);
        $user->setName('John Doe');
        $user->setEmail('john@example.com');

        return new JsonResponse($user->toArray());
    }
}
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

- `dto.xml` or `dtos.xml` - XML format
- `dto.yml` / `dto.yaml` or `dtos.yml` / `dtos.yaml` - YAML format
- `dto.php` or `dtos.php` - PHP format
- `dto/` subdirectory with multiple files

## License

MIT
