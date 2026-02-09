<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use JsonSerializable;
use PhpCollective\Dto\Dto\AbstractDto;

final class DtoMapper
{
    private function __construct()
    {
    }

    /**
     * @param array<string, mixed> $data
     * @param class-string<\PhpCollective\Dto\Dto\AbstractDto> $dtoClass
     * @param bool $ignoreMissing
     * @param string|null $type
     */
    public static function fromArray(
        array $data,
        string $dtoClass,
        bool $ignoreMissing = false,
        ?string $type = null,
    ): AbstractDto {
        return $dtoClass::createFromArray(
            data: $data,
            ignoreMissing: $ignoreMissing,
            type: $type,
        );
    }

    /**
     * @param iterable<int, mixed> $items
     * @param class-string<\PhpCollective\Dto\Dto\AbstractDto> $dtoClass
     * @param callable(mixed): mixed|null $normalizer
     * @param bool $ignoreMissing
     * @param string|null $type
     *
     * @return array<int, \PhpCollective\Dto\Dto\AbstractDto>
     */
    public static function fromIterable(
        iterable $items,
        string $dtoClass,
        ?callable $normalizer = null,
        bool $ignoreMissing = false,
        ?string $type = null,
    ): array {
        $mapped = [];

        foreach ($items as $item) {
            $mapped[] = self::normalizeItem(
                item: $item,
                dtoClass: $dtoClass,
                normalizer: $normalizer,
                ignoreMissing: $ignoreMissing,
                type: $type,
            );
        }

        return $mapped;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection<int, mixed> $collection
     * @param class-string<\PhpCollective\Dto\Dto\AbstractDto> $dtoClass
     * @param callable(mixed): mixed|null $normalizer
     * @param bool $ignoreMissing
     * @param string|null $type
     *
     * @return \Doctrine\Common\Collections\Collection<int, \PhpCollective\Dto\Dto\AbstractDto>
     */
    public static function fromCollection(
        Collection $collection,
        string $dtoClass,
        ?callable $normalizer = null,
        bool $ignoreMissing = false,
        ?string $type = null,
    ): Collection {
        return new ArrayCollection(self::fromIterable(
            items: $collection,
            dtoClass: $dtoClass,
            normalizer: $normalizer,
            ignoreMissing: $ignoreMissing,
            type: $type,
        ));
    }

    /**
     * @param iterable<int, mixed> $items
     * @param int $total
     * @param int $perPage
     * @param int $page
     * @param class-string<\PhpCollective\Dto\Dto\AbstractDto> $dtoClass
     * @param callable(mixed): mixed|null $normalizer
     * @param bool $ignoreMissing
     * @param string|null $type
     */
    public static function fromPaginated(
        iterable $items,
        int $total,
        int $perPage,
        int $page,
        string $dtoClass,
        ?callable $normalizer = null,
        bool $ignoreMissing = false,
        ?string $type = null,
    ): DtoPagination {
        return new DtoPagination(
            items: self::fromIterable(
                items: $items,
                dtoClass: $dtoClass,
                normalizer: $normalizer,
                ignoreMissing: $ignoreMissing,
                type: $type,
            ),
            total: $total,
            perPage: $perPage,
            page: $page,
        );
    }

    /**
     * @param mixed $item
     * @param class-string<\PhpCollective\Dto\Dto\AbstractDto> $dtoClass
     * @param callable(mixed): mixed|null $normalizer
     * @param bool $ignoreMissing
     * @param string|null $type
     *
     * @throws \InvalidArgumentException
     */
    private static function normalizeItem(
        mixed $item,
        string $dtoClass,
        ?callable $normalizer,
        bool $ignoreMissing,
        ?string $type,
    ): AbstractDto {
        if ($item instanceof AbstractDto) {
            return $item;
        }

        if (is_array($item)) {
            return self::fromArray(
                data: $item,
                dtoClass: $dtoClass,
                ignoreMissing: $ignoreMissing,
                type: $type,
            );
        }

        if (is_object($item)) {
            if (method_exists($item, 'toArray')) {
                $array = $item->toArray();
                if (!is_array($array)) {
                    throw new InvalidArgumentException('toArray() must return an array.');
                }

                return self::fromArray(
                    data: $array,
                    dtoClass: $dtoClass,
                    ignoreMissing: $ignoreMissing,
                    type: $type,
                );
            }

            if ($item instanceof JsonSerializable) {
                $array = $item->jsonSerialize();
                if (!is_array($array)) {
                    throw new InvalidArgumentException('jsonSerialize() must return an array.');
                }

                return self::fromArray(
                    data: $array,
                    dtoClass: $dtoClass,
                    ignoreMissing: $ignoreMissing,
                    type: $type,
                );
            }
        }

        if ($normalizer !== null) {
            $array = $normalizer($item);
            if (!is_array($array)) {
                throw new InvalidArgumentException('Normalizer must return an array.');
            }

            return self::fromArray(
                data: $array,
                dtoClass: $dtoClass,
                ignoreMissing: $ignoreMissing,
                type: $type,
            );
        }

        throw new InvalidArgumentException('DTO mapper expects arrays, DTOs, or normalizable objects.');
    }
}
