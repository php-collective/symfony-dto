<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Test\AutoMapper;

use AutoMapper\AutoMapperInterface;
use PhpCollective\SymfonyDto\AutoMapper\DtoAutoMapper;
use PhpCollective\SymfonyDto\AutoMapper\DtoAutoMapperInterface;
use PhpCollective\SymfonyDto\Test\Fixtures\UserDto;
use PhpCollective\SymfonyDto\Test\Fixtures\UserEntity;
use PHPUnit\Framework\TestCase;

class DtoAutoMapperTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $autoMapper = $this->createMock(AutoMapperInterface::class);
        $bridge = new DtoAutoMapper($autoMapper);

        $this->assertInstanceOf(DtoAutoMapperInterface::class, $bridge);
    }

    public function testToDtoMapsEntityToDto(): void
    {
        $entity = new UserEntity(1, 'John Doe', 'john@example.com');
        $expectedDto = new UserDto([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'active' => true,
        ]);

        $autoMapper = $this->createMock(AutoMapperInterface::class);
        $autoMapper->expects($this->once())
            ->method('map')
            ->with($entity, UserDto::class, [])
            ->willReturn($expectedDto);

        $bridge = new DtoAutoMapper($autoMapper);
        $result = $bridge->toDto($entity, UserDto::class);

        $this->assertSame($expectedDto, $result);
    }

    public function testToDtoPassesContext(): void
    {
        $entity = new UserEntity(1, 'Jane', 'jane@example.com');
        $context = ['groups' => ['read']];
        $expectedDto = new UserDto(['id' => 1, 'name' => 'Jane', 'email' => 'jane@example.com']);

        $autoMapper = $this->createMock(AutoMapperInterface::class);
        $autoMapper->expects($this->once())
            ->method('map')
            ->with($entity, UserDto::class, $context)
            ->willReturn($expectedDto);

        $bridge = new DtoAutoMapper($autoMapper);
        $result = $bridge->toDto($entity, UserDto::class, $context);

        $this->assertSame($expectedDto, $result);
    }

    public function testFromDtoUpdatesExistingEntity(): void
    {
        $dto = new UserDto(['id' => 1, 'name' => 'Updated Name', 'email' => 'updated@example.com']);
        $entity = new UserEntity(1, 'Old Name', 'old@example.com');
        $updatedEntity = new UserEntity(1, 'Updated Name', 'updated@example.com');

        $autoMapper = $this->createMock(AutoMapperInterface::class);
        $autoMapper->expects($this->once())
            ->method('map')
            ->with($dto, $entity, [])
            ->willReturn($updatedEntity);

        $bridge = new DtoAutoMapper($autoMapper);
        $result = $bridge->fromDto($dto, $entity);

        $this->assertSame($updatedEntity, $result);
    }

    public function testFromDtoToNewCreatesNewInstance(): void
    {
        $dto = new UserDto(['id' => 2, 'name' => 'New User', 'email' => 'new@example.com']);
        $expectedEntity = new UserEntity(2, 'New User', 'new@example.com');

        $autoMapper = $this->createMock(AutoMapperInterface::class);
        $autoMapper->expects($this->once())
            ->method('map')
            ->with($dto, UserEntity::class, [])
            ->willReturn($expectedEntity);

        $bridge = new DtoAutoMapper($autoMapper);
        $result = $bridge->fromDtoToNew($dto, UserEntity::class);

        $this->assertSame($expectedEntity, $result);
    }

    public function testToDtoCollectionMapsMultipleEntities(): void
    {
        $entities = [
            new UserEntity(1, 'User One', 'one@example.com'),
            new UserEntity(2, 'User Two', 'two@example.com'),
            new UserEntity(3, 'User Three', 'three@example.com'),
        ];

        $dtos = [
            new UserDto(['id' => 1, 'name' => 'User One', 'email' => 'one@example.com']),
            new UserDto(['id' => 2, 'name' => 'User Two', 'email' => 'two@example.com']),
            new UserDto(['id' => 3, 'name' => 'User Three', 'email' => 'three@example.com']),
        ];

        $autoMapper = $this->createMock(AutoMapperInterface::class);
        $autoMapper->expects($this->exactly(3))
            ->method('map')
            ->willReturnCallback(function ($source, $target, $context) use ($entities, $dtos) {
                $index = array_search($source, $entities, true);

                return $dtos[$index];
            });

        $bridge = new DtoAutoMapper($autoMapper);
        $result = $bridge->toDtoCollection($entities, UserDto::class);

        $this->assertCount(3, $result);
        $this->assertSame($dtos[0], $result[0]);
        $this->assertSame($dtos[1], $result[1]);
        $this->assertSame($dtos[2], $result[2]);
    }

    public function testToDtoCollectionHandlesEmptyIterable(): void
    {
        $autoMapper = $this->createMock(AutoMapperInterface::class);
        $autoMapper->expects($this->never())->method('map');

        $bridge = new DtoAutoMapper($autoMapper);
        $result = $bridge->toDtoCollection([], UserDto::class);

        $this->assertSame([], $result);
    }

    public function testToDtoCollectionPassesContextToEachMapping(): void
    {
        $entities = [
            new UserEntity(1, 'User', 'user@example.com'),
        ];

        $dto = new UserDto(['id' => 1, 'name' => 'User', 'email' => 'user@example.com']);
        $context = ['groups' => ['list']];

        $autoMapper = $this->createMock(AutoMapperInterface::class);
        $autoMapper->expects($this->once())
            ->method('map')
            ->with($entities[0], UserDto::class, $context)
            ->willReturn($dto);

        $bridge = new DtoAutoMapper($autoMapper);
        $result = $bridge->toDtoCollection($entities, UserDto::class, $context);

        $this->assertCount(1, $result);
        $this->assertSame($dto, $result[0]);
    }
}
