<?php

declare(strict_types=1);

namespace PhpCollective\SymfonyDto;

use Doctrine\Common\Collections\ArrayCollection;
use PhpCollective\Dto\Dto\Dto;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PhpCollectiveDtoBundle extends Bundle
{
    public function boot(): void
    {
        // Auto-configure Doctrine collections for DTO collection fields
        Dto::setCollectionFactory(fn (array $items) => new ArrayCollection($items));
    }
}
