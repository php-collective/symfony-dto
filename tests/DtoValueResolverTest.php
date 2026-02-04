<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Test;

use PhpCollective\SymfonyDto\Attribute\MapRequestDto;
use PhpCollective\SymfonyDto\Http\DtoValueResolver;
use PhpCollective\SymfonyDto\Test\Fixtures\TestDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DtoValueResolverTest extends TestCase
{
    public function testResolveFromQuerySource(): void
    {
        $request = Request::create('/users', 'GET', ['name' => 'Mark']);
        $argument = new ArgumentMetadata(
            'dto',
            TestDto::class,
            false,
            false,
            null,
            false,
            [new MapRequestDto(MapRequestDto::SOURCE_QUERY)],
        );

        $resolver = new DtoValueResolver();
        $resolved = iterator_to_array($resolver->resolve($request, $argument), false);

        $this->assertCount(1, $resolved);
        $this->assertSame(['name' => 'Mark'], $resolved[0]->data);
    }

    public function testResolveFromBodySource(): void
    {
        $request = Request::create(
            '/users',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'Mark'], JSON_THROW_ON_ERROR),
        );
        $argument = new ArgumentMetadata(
            'dto',
            TestDto::class,
            false,
            false,
            null,
            false,
            [new MapRequestDto(MapRequestDto::SOURCE_BODY)],
        );

        $resolver = new DtoValueResolver();
        $resolved = iterator_to_array($resolver->resolve($request, $argument), false);

        $this->assertCount(1, $resolved);
        $this->assertSame(['name' => 'Mark'], $resolved[0]->data);
    }

    public function testResolveUsesAutoSourceByDefault(): void
    {
        $request = Request::create('/users', 'GET', ['name' => 'Mark']);
        $argument = new ArgumentMetadata(
            'dto',
            TestDto::class,
            false,
            false,
            null,
            false,
            [],
        );

        $resolver = new DtoValueResolver();
        $resolved = iterator_to_array($resolver->resolve($request, $argument), false);

        $this->assertCount(1, $resolved);
        $this->assertSame(['name' => 'Mark'], $resolved[0]->data);
    }
}
