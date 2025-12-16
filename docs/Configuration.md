# Configuration

## Bundle Configuration

Create `config/packages/php_collective_dto.yaml`:

```yaml
php_collective_dto:
    config_path: config          # Path to DTO config files (relative to project root)
    output_path: src/Dto         # Path for generated DTOs
    namespace: App\Dto           # Namespace for generated DTOs
```

## DTO Definition Formats

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

### PHP Format

Create `config/dto.php`:

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
    config_path: config/dto
```

## Further Reading

See the main [php-collective/dto documentation](https://github.com/php-collective/dto) for complete configuration options.
