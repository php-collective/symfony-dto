<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Attribute;

use Attribute;

/**
 * Attribute to map request data to a DTO in controller actions.
 *
 * Usage:
 *
 * ```php
 * #[Route('/users', methods: ['POST'])]
 * public function create(#[MapRequestDto] CreateUserDto $dto): Response
 * {
 *     // $dto is automatically created from request body/query data
 * }
 * ```
 *
 * Options:
 * - source: Where to get the data from ('body', 'query', 'request', 'auto')
 *   - 'body': JSON body only (for POST/PUT/PATCH)
 *   - 'query': Query parameters only
 *   - 'request': All request parameters
 *   - 'auto': Automatically detect based on request method (default)
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class MapRequestDto
{
    /**
     * @var string
     */
    public const SOURCE_BODY = 'body';

    /**
     * @var string
     */
    public const SOURCE_QUERY = 'query';

    /**
     * @var string
     */
    public const SOURCE_REQUEST = 'request';

    /**
     * @var string
     */
    public const SOURCE_AUTO = 'auto';

    /**
     * @param string $source Data source: 'body', 'query', 'request', or 'auto'
     */
    public function __construct(
        public readonly string $source = self::SOURCE_AUTO,
    ) {
    }
}
