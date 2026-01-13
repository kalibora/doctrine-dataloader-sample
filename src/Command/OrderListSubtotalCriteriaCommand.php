<?php

namespace App\Command;

use App\Entity\Order;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(
    name: 'app:order:list:subtotal-criteria',
    description: 'Show a list of orders with only the highest-subtotal line item using Criteria.',
)]
final class OrderListSubtotalCriteriaCommand extends AbstractOrderListCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->addOption('eager', null, InputOption::VALUE_NONE, 'Fetch LineItem and Product with eager loading.');
    }

    protected function isShowTotal(): bool
    {
        return false;
    }

    protected function findOrders(InputInterface $input): array
    {
        $qb = $this->orderRepository->createQueryBuilder('o');

        if ($input->getOption('eager')) {
            $qb->leftJoin('o.lineItems', 'li')->addSelect('li')
                ->leftJoin('li.product', 'p')->addSelect('p');
        }

        return $qb->getQuery()->getResult();
    }

    protected function extractLineItems(Order $order): iterable
    {
        $lineItem = $order->getHighestSubtotalLineItemUsingCriteria();

        return null === $lineItem ? [] : [$lineItem];
    }
}
