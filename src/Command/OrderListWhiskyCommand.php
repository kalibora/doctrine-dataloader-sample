<?php

namespace App\Command;

use App\Entity\AlcoholType;
use App\Entity\Order;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(
    name: 'app:order:list:whisky',
    description: 'Whiskyの明細のみの注文の一覧を表示する',
)]
final class OrderListWhiskyCommand extends AbstractOrderListCommand
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
        return $order->getLineItemsByAlcoholType(AlcoholType::WHISKY);
    }
}
