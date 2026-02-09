<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Mapper;

use PhpCollective\Dto\Dto\AbstractDto;

final class DtoPagination
{
    /**
     * @param array<int, \PhpCollective\Dto\Dto\AbstractDto> $items
     * @param int $page
     * @param int $perPage
     * @param int $total
     */
    public function __construct(
        private array $items,
        private int $total,
        private int $perPage,
        private int $page,
    ) {
    }

    /**
     * @return array<int, \PhpCollective\Dto\Dto\AbstractDto>
     */
    public function items(): array
    {
        return $this->items;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function page(): int
    {
        return $this->page;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'data' => array_map(
                static fn (AbstractDto $dto): array => $dto->toArray(),
                $this->items,
            ),
            'meta' => [
                'total' => $this->total,
                'perPage' => $this->perPage,
                'page' => $this->page,
            ],
        ];
    }
}
