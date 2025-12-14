<?php

namespace App\Service\DataLoader\Loader;

use App\Entity\LineItem;
use App\Entity\Order;
use App\Repository\LineItemRepository;
use App\Service\DataLoader\ArgsInterface;
use App\Service\DataLoader\LoaderInterface;

/**
 * @implements LoaderInterface<NoArgs, LineItem>
 */
final class OrderHighestSubtotalLineItemLoader implements LoaderInterface
{
    public function __construct(
        private readonly LineItemRepository $lineItemRepository,
    ) {
    }

    public static function getSupportedEntity(): string
    {
        return Order::class;
    }

    public function createArgs(array $rawArgs): ArgsInterface
    {
        return NoArgs::fromArray($rawArgs);
    }

    public function load(array $ids, ArgsInterface $args): array
    {
        return $this->lineItemRepository->findHighestSubtotalLineItemMap($ids);
    }
}
