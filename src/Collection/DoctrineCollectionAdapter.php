<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto\Collection;

use PhpCollective\Dto\Collection\CollectionAdapterInterface;

/**
 * Adapter for Doctrine's ArrayCollection class.
 *
 * Doctrine\Common\Collections\ArrayCollection is mutable - add() modifies in place.
 */
class DoctrineCollectionAdapter implements CollectionAdapterInterface
{
    /**
     * @inheritDoc
     */
    public function getCollectionClass(): string
    {
        return '\\Doctrine\\Common\\Collections\\ArrayCollection';
    }

    /**
     * @inheritDoc
     */
    public function isImmutable(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getAppendMethod(): string
    {
        return 'add';
    }

    /**
     * @inheritDoc
     */
    public function getCreateEmptyCode(string $typeHint): string
    {
        return "new {$typeHint}([])";
    }

    /**
     * @inheritDoc
     */
    public function getAppendCode(string $collectionVar, string $itemVar): string
    {
        return "{$collectionVar}->add({$itemVar});";
    }
}
