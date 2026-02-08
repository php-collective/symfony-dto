# Usage Guide

## Controller Integration

### From Request Data

```php
use App\Dto\UserDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends AbstractController
{
    #[Route('/users', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new UserDto($data);

        return $this->json($dto->toArray());
    }

    #[Route('/users/{id}', methods: ['PATCH'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        // Use ignoreMissing for partial updates
        $dto = UserDto::createFromArray($data, ignoreMissing: true);

        return $this->json($dto->toArray());
    }
}
```

### Automatic DTO Resolution

Enable the value resolver in config (`enable_value_resolver: true`) and use `#[MapRequestDto]`:

```php
use App\Dto\UserDto;
use PhpCollective\SymfonyDto\Attribute\MapRequestDto;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends AbstractController
{
    #[Route('/users', methods: ['POST'])]
    public function store(#[MapRequestDto] UserDto $dto): JsonResponse
    {
        return $this->json($dto->toArray());
    }
}
```

Choose a specific source if needed:

```php
#[MapRequestDto(source: MapRequestDto::SOURCE_QUERY)]
```

### From Doctrine Entities

```php
#[Route('/users/{id}', methods: ['GET'])]
public function show(User $user): JsonResponse
{
    // Convert entity to DTO
    $dto = new UserDto([
        'id' => $user->getId(),
        'name' => $user->getName(),
        'email' => $user->getEmail(),
    ]);

    return $this->json($dto->toArray());
}
```

## Collections

The `PhpCollectiveDtoBundle` automatically registers Doctrine's `ArrayCollection` as the collection type for DTO collection fields. No manual setup is needed.

### Defining Collection Fields

In your DTO config, use the `[]` suffix to define collection fields:

```xml
<dto name="User">
    <field name="id" type="int"/>
    <field name="name" type="string"/>
    <field name="roles" type="Role[]"/>
    <field name="tags" type="string[]"/>
</dto>
```

After generating, collection fields will use Doctrine's `ArrayCollection` class:

```php
$user = new UserDto([
    'id' => 1,
    'name' => 'John',
    'roles' => [
        ['name' => 'admin', 'active' => true],
        ['name' => 'editor', 'active' => false],
    ],
    'tags' => ['vip', 'premium'],
]);

// Doctrine Collection methods are available
$activeRoles = $user->getRoles()->filter(fn (RoleDto $role) => $role->getActive());
$firstRole = $user->getRoles()->first();
$tagCount = $user->getTags()->count();
```

### What the Adapter Does

The bundle performs two registrations on boot:

1. **Runtime collection factory** — `Dto::setCollectionFactory(fn (array $items) => new ArrayCollection($items))` ensures that collection fields are hydrated as `Doctrine\Common\Collections\ArrayCollection` instances at runtime.
2. **Code generation adapter** — `CollectionAdapterRegistry::register(new DoctrineCollectionAdapter())` ensures that generated DTO code uses `new ArrayCollection([])` and `->add()` for collection initialization and appending.

Both are required: the factory handles runtime hydration from arrays, while the adapter controls the generated PHP code.

## Validation

Use Symfony's Validator component:

```php
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UserController extends AbstractController
{
    #[Route('/users', methods: ['POST'])]
    public function store(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new UserDto($data);

        $violations = $validator->validate($dto->toArray(), new Assert\Collection([
            'name' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
            'email' => [new Assert\NotBlank(), new Assert\Email()],
        ]));

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return $this->json(['errors' => $errors], 422);
        }

        return $this->json($dto->toArray());
    }
}
```

### Validation Bridge

If your DTOs use the built-in validation rules from `php-collective/dto` (e.g. `required`, `minLength`, `maxLength`, `min`, `max`, `pattern`), you can automatically convert them to Symfony Validator constraints:

```php
composer require symfony/validator
```

```php
use PhpCollective\SymfonyDto\Validation\DtoConstraintBuilder;
use Symfony\Component\Validator\Validation;

$dto = new UserDto();
$constraint = DtoConstraintBuilder::fromDto($dto);

$validator = Validation::createValidator();
$violations = $validator->validate($data, $constraint);

if (count($violations) > 0) {
    // Handle validation errors
}
```

The bridge maps DTO rules to Symfony constraints:

| DTO Rule | Symfony Constraint |
|---|---|
| `required` | `Assert\NotBlank` (wrapped in `Assert\Required`) |
| `minLength` / `maxLength` | `Assert\Length(min:, max:)` |
| `min` / `max` | `Assert\Range(min:, max:)` |
| `pattern` | `Assert\Regex(pattern:)` |

Optional fields are wrapped in `Assert\Optional`, required fields in `Assert\Required`.
Extra fields not defined in the DTO are allowed by default.

## Service Layer Pattern

```php
// src/Service/UserService.php
use App\Dto\UserDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function createUser(UserDto $dto): User
    {
        $user = new User();
        $user->setName($dto->getName());
        $user->setEmail($dto->getEmail());

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
```

## Doctrine Integration

### Query Results to DTOs

```php
// src/Repository/UserRepository.php
use App\Dto\UserSummaryDto;

class UserRepository extends ServiceEntityRepository
{
    /**
     * @return UserSummaryDto[]
     */
    public function findActiveSummaries(): array
    {
        $rows = $this->createQueryBuilder('u')
            ->select('u.id', 'u.name', 'u.email')
            ->where('u.active = true')
            ->getQuery()
            ->getArrayResult();

        return array_map(fn($row) => new UserSummaryDto($row), $rows);
    }
}
```

## Nested DTOs

When your DTO has nested DTO fields:

```php
// config/dto.xml
<dto name="Order">
    <field name="id" type="int"/>
    <field name="customer" type="Customer"/>
    <field name="items" type="OrderItem[]"/>
</dto>

// Usage
$order = new OrderDto([
    'id' => 1,
    'customer' => ['name' => 'John', 'email' => 'john@example.com'],
    'items' => [
        ['product' => 'Widget', 'quantity' => 2],
        ['product' => 'Gadget', 'quantity' => 1],
    ],
]);

// Access nested data
$customerName = $order->getCustomer()->getName();
```

## Further Reading

See the main [php-collective/dto documentation](https://github.com/php-collective/dto) for:
- DTO configuration options
- Type support
- Custom casters
- Advanced patterns
