<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto;

use Doctrine\Common\Collections\ArrayCollection;
use PhpCollective\Dto\Collection\CollectionAdapterRegistry;
use PhpCollective\Dto\Dto\Dto;
use PhpCollective\SymfonyDto\Collection\DoctrineCollectionAdapter;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PhpCollectiveDtoBundle extends Bundle
{
    public function boot(): void
    {
        // Auto-configure Doctrine collections for DTO collection fields
        Dto::setCollectionFactory(fn (array $items) => new ArrayCollection($items));

        // Register Doctrine collection adapter for proper template generation
        CollectionAdapterRegistry::register(new DoctrineCollectionAdapter());
    }
}
