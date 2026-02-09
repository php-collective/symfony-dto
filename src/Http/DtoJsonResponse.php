<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Http;

use InvalidArgumentException;
use JsonSerializable;
use PhpCollective\Dto\Dto\AbstractDto;
use Symfony\Component\HttpFoundation\JsonResponse;

class DtoJsonResponse extends JsonResponse
{
    /**
     * @param \PhpCollective\Dto\Dto\AbstractDto $dto
     * @param int $status
     * @param array<string, string> $headers
     * @param bool $json
     */
    public static function fromDto(
        AbstractDto $dto,
        int $status = 200,
        array $headers = [],
        bool $json = false,
    ): self {
        return new self($dto->toArray(), $status, $headers, $json);
    }

    /**
     * @param iterable<int, mixed> $items
     * @param int $status
     * @param array<string, string> $headers
     * @param bool $json
     *
     * @throws \InvalidArgumentException
     */
    public static function fromCollection(
        iterable $items,
        int $status = 200,
        array $headers = [],
        bool $json = false,
    ): self {
        $data = [];

        foreach ($items as $item) {
            if ($item instanceof AbstractDto) {
                $data[] = $item->toArray();

                continue;
            }

            if (is_array($item)) {
                $data[] = $item;

                continue;
            }

            if (is_object($item) && method_exists($item, 'toArray')) {
                $array = $item->toArray();
                if (!is_array($array)) {
                    throw new InvalidArgumentException('toArray() must return an array.');
                }
                $data[] = $array;

                continue;
            }

            if ($item instanceof JsonSerializable) {
                $array = $item->jsonSerialize();
                if (!is_array($array)) {
                    throw new InvalidArgumentException('jsonSerialize() must return an array.');
                }
                $data[] = $array;

                continue;
            }

            throw new InvalidArgumentException('DtoJsonResponse expects DTOs, arrays, or array-like objects.');
        }

        return new self($data, $status, $headers, $json);
    }
}
