<?php

namespace App\EntityListener;

use App\Entity\AlcoholType;
use App\Entity\Order;
use App\Service\DataLoader\Loader;
use App\Service\DataLoader\Resolver;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Mapping as ORM;

final class OrderListener
{
    public function __construct(
        private readonly Resolver $resolver,
    ) {
    }

    #[ORM\PostLoad]
    public function postLoadHandler(Order $order, PostLoadEventArgs $args): void
    {
        $id = $order->getId();

        $this->resolver->addId(Order::class, $id);

        $order->setHighestSubtotalLineItemResolver(
            fn () => $this->resolver->resolve(Loader\OrderHighestSubtotalLineItemLoader::class, $id, []),
        );

        $order->setLineItemsByAlcoholTypeResolver(
            fn (AlcoholType $t) => $this->resolver->resolve(Loader\OrderLineItemsByAlcoholTypeLoader::class, $id, [$t]),
        );
    }
}
