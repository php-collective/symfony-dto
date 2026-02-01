# Configuration

## Bundle Configuration

Create `config/packages/php_collective_dto.yaml`:

```yaml
php_collective_dto:
    config_path: config/          # Path to DTO config files (relative to project root)
    output_path: src/Dto/        # Path for generated DTOs
    namespace: App\Dto           # Namespace for generated DTOs
```

## DTO Definition Formats

### PHP Format (default)

Create `config/dtos.php`.
This is the default format when running `bin/console dto:init`:

```php
use PhpCollective\Dto\Builder\Dto;
use PhpCollective\Dto\Builder\Field;
use PhpCollective\Dto\Builder\Schema;

return Schema::create()
    ->dto(Dto::create('User')->fields(
        Field::int('id'),
        Field::string('name'),
        Field::string('email')->nullable(),
        Field::array('roles', 'Role'),
    ))
    ->dto(Dto::create('Role')->fields(
        Field::int('id'),
        Field::string('name'),
    ))
    ->toArray();
```

### XML Format

Create `config/dto.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<dtos xmlns="php-collective-dto">
    <dto name="User">
        <field name="id" type="int"/>
        <field name="name" type="string"/>
        <field name="email" type="string" nullable="true"/>
        <field name="roles" type="Role[]"/>
    </dto>

    <dto name="Role">
        <field name="id" type="int"/>
        <field name="name" type="string"/>
    </dto>
</dtos>
```

### YAML Format

Create `config/dto.yml` or `config/dto.yaml`:

```yaml
User:
  fields:
    id: int
    name: string
    email: string?
    roles: Role[]

Role:
  fields:
    id: int
    name: string
```

## Command Options

The `dto:generate` command supports:

```bash
# Preview changes without writing
bin/console dto:generate --dry-run

# Override configuration
bin/console dto:generate --config-path=config/custom --output-path=src/CustomDto --namespace=App\\CustomDto

# Verbose output
bin/console dto:generate -v

# Combine options
bin/console dto:generate --dry-run -v
```

## Directory Structure

Recommended structure:

```
config/
├── packages/
│   └── php_collective_dto.yaml  # Bundle config
└── dto.xml                      # DTO definitions
src/
└── Dto/
    ├── UserDto.php              # Generated
    └── RoleDto.php              # Generated
```

## Multiple Config Files

You can organize DTOs in a subdirectory:

```
config/
└── dto/
    ├── user.xml
    ├── order.xml
    └── product.xml
```

Update bundle config:

```yaml
php_collective_dto:
    config_path: config/dto/
```

## Exclude Generated DTOs from Static Analysis

Generated code usually shouldn't run through code-style or static analysis checks.

### PHP_CodeSniffer

Add an exclude pattern to your `phpcs.xml`:

```xml
<rule ref="...">
    <exclude-pattern>src/Dto/*</exclude-pattern>
</rule>
```

### PHPStan

Add an exclude path to your `phpstan.neon`:

```yaml
parameters:
    excludePaths:
        - src/Dto/
```

Alternatively, you can avoid exclusions altogether by generating DTOs into a separate directory outside `src/` (e.g. `generated/`). This requires a custom PSR-4 autoload entry in your `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "App\\Dto\\": "generated/"
        }
    }
}
```

See the base package's [SeparatingGeneratedCode.md](https://github.com/php-collective/dto/blob/master/docs/SeparatingGeneratedCode.md) for details.

## Composer Scripts

You can add convenience scripts to your `composer.json`:

```json
{
    "scripts": {
        "dto:generate": "bin/console dto:generate",
        "dto:check": "bin/console dto:generate --dry-run"
    }
}
```

## Further Reading

See the main [php-collective/dto documentation](https://github.com/php-collective/dto) for complete configuration options.
