<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Test;

use Doctrine\Common\Collections\ArrayCollection;
use PhpCollective\SymfonyDto\Mapper\DtoMapper;
use PhpCollective\SymfonyDto\Mapper\DtoPagination;
use PhpCollective\SymfonyDto\Test\Fixtures\TestDto;
use PHPUnit\Framework\TestCase;

class DtoMapperTest extends TestCase
{
    public function testFromArrayCreatesDto(): void
    {
        $dto = DtoMapper::fromArray(['name' => 'Mark'], TestDto::class);

        $this->assertInstanceOf(TestDto::class, $dto);
        $this->assertSame(['name' => 'Mark'], $dto->data);
    }

    public function testFromIterableNormalizesObjects(): void
    {
        $object = new class () {
            public function toArray(): array
            {
                return ['name' => 'Anna'];
            }
        };

        $dtos = DtoMapper::fromIterable([$object], TestDto::class);

        $this->assertSame('Anna', $dtos[0]->data['name']);
    }

    public function testFromCollectionReturnsDoctrineCollection(): void
    {
        $collection = new ArrayCollection([['name' => 'Jane']]);

        $dtos = DtoMapper::fromCollection($collection, TestDto::class);

        $this->assertInstanceOf(ArrayCollection::class, $dtos);
        $this->assertSame('Jane', $dtos->first()->data['name']);
    }

    public function testFromPaginatedReturnsDtoPagination(): void
    {
        $pagination = DtoMapper::fromPaginated(
            items: [
                ['name' => 'B'],
            ],
            total: 2,
            perPage: 1,
            page: 2,
            dtoClass: TestDto::class,
        );

        $this->assertInstanceOf(DtoPagination::class, $pagination);
        $this->assertSame('B', $pagination->items()[0]->data['name']);
        $this->assertSame(2, $pagination->total());
    }
}
