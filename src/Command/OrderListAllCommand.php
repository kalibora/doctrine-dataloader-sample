<?php

namespace App\Command;

use App\Entity\Order;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(
    name: 'app:order:list:all',
    description: '全明細を含む注文の一覧を表示する',
)]
final class OrderListAllCommand extends AbstractOrderListCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->addOption('eager', null, InputOption::VALUE_NONE, 'LineItem, Product を Eager Loading で取得する');
    }

    protected function isShowTotal(): bool
    {
        return true;
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
        return $order->getLineItems();
    }
}
