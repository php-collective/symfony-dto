<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Test;

use PhpCollective\SymfonyDto\Http\DtoJsonResponse;
use PhpCollective\SymfonyDto\Test\Fixtures\TestDto;
use PHPUnit\Framework\TestCase;

class DtoJsonResponseTest extends TestCase
{
    public function testFromDtoReturnsJsonPayload(): void
    {
        $response = DtoJsonResponse::fromDto(new TestDto(['name' => 'Mark']));

        $this->assertSame('{"name":"Mark"}', $response->getContent());
    }

    public function testFromCollectionReturnsJsonPayload(): void
    {
        $response = DtoJsonResponse::fromCollection([
            new TestDto(['name' => 'Anna']),
        ]);

        $this->assertSame('[{"name":"Anna"}]', $response->getContent());
    }
}
