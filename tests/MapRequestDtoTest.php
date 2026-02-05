<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Test;

use Attribute;
use PhpCollective\SymfonyDto\Attribute\MapRequestDto;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class MapRequestDtoTest extends TestCase
{
    public function testDefaultSource(): void
    {
        $attribute = new MapRequestDto();

        $this->assertSame(MapRequestDto::SOURCE_AUTO, $attribute->source);
    }

    public function testBodySource(): void
    {
        $attribute = new MapRequestDto(source: MapRequestDto::SOURCE_BODY);

        $this->assertSame('body', $attribute->source);
    }

    public function testQuerySource(): void
    {
        $attribute = new MapRequestDto(source: MapRequestDto::SOURCE_QUERY);

        $this->assertSame('query', $attribute->source);
    }

    public function testRequestSource(): void
    {
        $attribute = new MapRequestDto(source: MapRequestDto::SOURCE_REQUEST);

        $this->assertSame('request', $attribute->source);
    }

    public function testIsAttribute(): void
    {
        $reflection = new ReflectionClass(MapRequestDto::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertNotEmpty($attributes);
    }

    public function testConstants(): void
    {
        $this->assertSame('body', MapRequestDto::SOURCE_BODY);
        $this->assertSame('query', MapRequestDto::SOURCE_QUERY);
        $this->assertSame('request', MapRequestDto::SOURCE_REQUEST);
        $this->assertSame('auto', MapRequestDto::SOURCE_AUTO);
    }
}
