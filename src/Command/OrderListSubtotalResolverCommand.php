<?php

namespace App\Command;

use App\Entity\Order;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(
    name: 'app:order:list:subtotal-resolver',
    description: 'Show a list of orders with only the highest-subtotal line item using the Resolver.',
)]
final class OrderListSubtotalResolverCommand extends AbstractOrderListCommand
{
    protected function isShowTotal(): bool
    {
        return false;
    }

    protected function findOrders(InputInterface $input): array
    {
        return $this->orderRepository->findAll();
    }

    protected function extractLineItems(Order $order): iterable
    {
        $lineItem = $order->getHighestSubtotalLineItemUsingResolver();

        return null === $lineItem ? [] : [$lineItem];
    }
}
