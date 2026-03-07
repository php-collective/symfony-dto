<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\AutoMapper;

use AutoMapper\AutoMapperInterface;
use PhpCollective\Dto\Dto\AbstractDto;

/**
 * Bridge between php-collective/dto and jolicode/automapper.
 *
 * Provides automatic object-to-DTO mapping using AutoMapper's code generation,
 * eliminating manual field-by-field mapping code while maintaining high performance.
 *
 * @see https://automapper.jolicode.com/
 */
final class DtoAutoMapper implements DtoAutoMapperInterface
{
    public function __construct(private AutoMapperInterface $autoMapper)
    {
    }

    /**
     * @inheritDoc
     */
    public function toDto(object $source, string $dtoClass, array $context = []): AbstractDto
    {
        /** @var \PhpCollective\Dto\Dto\AbstractDto $result */
        $result = $this->autoMapper->map($source, $dtoClass, $context);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function fromDto(AbstractDto $dto, object $target, array $context = []): object
    {
        /** @var object $result */
        $result = $this->autoMapper->map($dto, $target, $context);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function fromDtoToNew(AbstractDto $dto, string $targetClass, array $context = []): object
    {
        /** @var object $result */
        $result = $this->autoMapper->map($dto, $targetClass, $context);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function toDtoCollection(iterable $sources, string $dtoClass, array $context = []): array
    {
        $result = [];

        foreach ($sources as $source) {
            $result[] = $this->toDto($source, $dtoClass, $context);
        }

        return $result;
    }
}
