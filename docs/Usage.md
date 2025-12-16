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

## Collection Factory

Configure collection factory in a kernel listener or service:

```php
// src/EventListener/DtoListener.php
use Doctrine\Common\Collections\ArrayCollection;
use PhpCollective\Dto\Dto\Dto;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class DtoListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        Dto::setCollectionFactory(fn (array $items) => new ArrayCollection($items));
    }
}
```

Register in `services.yaml`:

```yaml
services:
    App\EventListener\DtoListener:
        tags:
            - { name: kernel.event_listener, event: kernel.request, priority: 100 }
```

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
            'name' => [new Assert\NotBlank(), new Assert\Length(['max' => 255])],
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
