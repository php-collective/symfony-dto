<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Http;

use PhpCollective\Dto\Dto\AbstractDto;
use PhpCollective\SymfonyDto\Attribute\MapRequestDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Resolves DTO arguments in controller actions from request data.
 *
 * This resolver automatically creates DTO instances for controller parameters
 * that are typed as AbstractDto subclasses and have the #[MapRequestDto] attribute.
 *
 * Usage:
 *
 * ```php
 * use PhpCollective\SymfonyDto\Attribute\MapRequestDto;
 *
 * #[Route('/users', methods: ['POST'])]
 * public function create(#[MapRequestDto] CreateUserDto $dto): Response
 * {
 *     // $dto is automatically created from request data
 * }
 * ```
 *
 * Without the attribute, the resolver will still work for AbstractDto subclasses
 * but with default 'auto' source detection.
 */
class DtoValueResolver implements ValueResolverInterface
{
    /**
     * @return iterable<\PhpCollective\Dto\Dto\AbstractDto>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        // Only resolve AbstractDto subclasses
        if ($type === null || !class_exists($type) || !is_subclass_of($type, AbstractDto::class)) {
            return [];
        }

        // Get the attribute if present
        $attribute = $this->getAttribute($argument);

        // If no attribute and not nullable, still try to resolve
        // This allows DTOs to be used without explicit attribute
        if ($attribute === null && !$argument->isNullable()) {
            $attribute = new MapRequestDto();
        }

        if ($attribute === null) {
            return [];
        }

        $data = $this->extractData($request, $attribute);

        /** @var class-string<\PhpCollective\Dto\Dto\AbstractDto> $dtoClass */
        $dtoClass = $type;

        yield $dtoClass::createFromArray($data);
    }

    /**
     * Get the MapRequestDto attribute from the argument.
     */
    private function getAttribute(ArgumentMetadata $argument): ?MapRequestDto
    {
        /** @var array<\PhpCollective\SymfonyDto\Attribute\MapRequestDto> $attributes */
        $attributes = $argument->getAttributes(MapRequestDto::class, ArgumentMetadata::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            return $attribute;
        }

        return null;
    }

    /**
     * Extract data from the request based on the source configuration.
     *
     * @return array<string, mixed>
     */
    private function extractData(Request $request, MapRequestDto $attribute): array
    {
        return match ($attribute->source) {
            MapRequestDto::SOURCE_BODY => $this->extractBodyData($request),
            MapRequestDto::SOURCE_QUERY => $request->query->all(),
            MapRequestDto::SOURCE_REQUEST => $request->request->all(),
            default => $this->extractAutoData($request),
        };
    }

    /**
     * Extract data from request body (JSON or form data).
     *
     * @return array<string, mixed>
     */
    private function extractBodyData(Request $request): array
    {
        $contentType = $request->getContentTypeFormat();

        if ($contentType === 'json') {
            $content = $request->getContent();
            if ($content === '') {
                return [];
            }

            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            return $decoded;
        }

        return $request->request->all();
    }

    /**
     * Automatically detect the best data source based on request method and content.
     *
     * @return array<string, mixed>
     */
    private function extractAutoData(Request $request): array
    {
        // For GET and HEAD, use query parameters
        if (in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return $request->query->all();
        }

        // For other methods, prefer body data
        $bodyData = $this->extractBodyData($request);
        if ($bodyData !== []) {
            return $bodyData;
        }

        // Fall back to query parameters
        return $request->query->all();
    }
}
