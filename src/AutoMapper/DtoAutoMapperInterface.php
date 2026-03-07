<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\AutoMapper;

use PhpCollective\Dto\Dto\AbstractDto;

/**
 * Interface for automatic object-to-DTO mapping using AutoMapper.
 *
 * This bridge provides bidirectional mapping between domain objects (entities)
 * and generated DTOs, eliminating manual field-by-field mapping code.
 */
interface DtoAutoMapperInterface
{
    /**
     * Map a source object to a DTO class.
     *
     * @param object $source The source object to map from (e.g., Doctrine entity)
     * @param class-string<\PhpCollective\Dto\Dto\AbstractDto> $dtoClass The target DTO class
     * @param array<string, mixed> $context Additional mapping context
     *
     * @return \PhpCollective\Dto\Dto\AbstractDto
     */
    public function toDto(object $source, string $dtoClass, array $context = []): AbstractDto;

    /**
     * Map a DTO to a target object, updating it in place.
     *
     * @param \PhpCollective\Dto\Dto\AbstractDto $dto The source DTO
     * @param object $target The target object to update (e.g., Doctrine entity)
     * @param array<string, mixed> $context Additional mapping context
     *
     * @return object The updated target object
     */
    public function fromDto(AbstractDto $dto, object $target, array $context = []): object;

    /**
     * Map a DTO to a new instance of a target class.
     *
     * @param \PhpCollective\Dto\Dto\AbstractDto $dto The source DTO
     * @param class-string $targetClass The target class to instantiate
     * @param array<string, mixed> $context Additional mapping context
     *
     * @return object
     */
    public function fromDtoToNew(AbstractDto $dto, string $targetClass, array $context = []): object;

    /**
     * Map an iterable of source objects to DTOs.
     *
     * @param iterable<object> $sources The source objects to map
     * @param class-string<\PhpCollective\Dto\Dto\AbstractDto> $dtoClass The target DTO class
     * @param array<string, mixed> $context Additional mapping context
     *
     * @return array<int, \PhpCollective\Dto\Dto\AbstractDto>
     */
    public function toDtoCollection(iterable $sources, string $dtoClass, array $context = []): array;
}
