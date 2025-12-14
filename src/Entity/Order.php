<?php

namespace App\Entity;

use App\EntityListener\OrderListener;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\EntityListeners([OrderListener::class])]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private \DateTimeImmutable $orderedAt;

    /**
     * @var Collection<int, LineItem>
     */
    #[ORM\OneToMany(mappedBy: 'order', targetEntity: LineItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $lineItems;

    /**
     * (Closure():?LineItem)|null
     */
    private ?\Closure $highestSubtotalLineItemResolver = null;

    /**
     * (Closure(AlcoholType):list<LineItem>)|null
     */
    private ?\Closure $lineItemsByAlcoholTypeResolver = null;

    public function __construct(
        \DateTimeImmutable $orderedAt,
    ) {
        $this->orderedAt = $orderedAt;
        $this->lineItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderedAt(): ?\DateTimeImmutable
    {
        return $this->orderedAt;
    }

    public function addProduct(Product $product, int $quantity): void
    {
        $lineNumber = $this->lineItems->count() + 1;
        $lineItem = new LineItem($this, $product, $lineNumber, $quantity, $product->getPrice());

        $this->lineItems->add($lineItem);
    }

    /**
     * @return Collection<int, LineItem>
     */
    public function getLineItems(): Collection
    {
        return $this->lineItems;
    }

    /**
     * Criteria を用いて subtotal が最大のLineItemを返す
     */
    public function getHighestSubtotalLineItemUsingCriteria(): ?LineItem
    {
        $criteria = Criteria::create(true)
            ->orderBy([
                'subTotal' => Criteria::DESC,
                'id' => Criteria::ASC, // 安定ソートのためにIDでのソートを追加
            ])
            ->setMaxResults(1);

        $lineItems = $this->lineItems->matching($criteria);

        return $lineItems->isEmpty() ? null : $lineItems->first();
    }

    /**
     * Resolver を用いて subtotal が最大のLineItemを返す
     */
    public function getHighestSubtotalLineItemUsingResolver(): ?LineItem
    {
        if (null === $this->highestSubtotalLineItemResolver) {
            throw new \LogicException('HighestSubtotalLineItemResolver is not set.');
        }

        return ($this->highestSubtotalLineItemResolver)();
    }

    /**
     * 指定されたAlcoholTypeのLineItemの配列を返す
     *
     * @return list<LineItem>
     */
    public function getLineItemsByAlcoholType(AlcoholType $alcoholType): array
    {
        if (null === $this->lineItemsByAlcoholTypeResolver) {
            throw new \LogicException('LineItemsByAlcoholTypeResolver is not set.');
        }

        return ($this->lineItemsByAlcoholTypeResolver)($alcoholType) ?? [];
    }

    public function getTotal(): int
    {
        $total = 0;

        foreach ($this->lineItems as $lineItem) {
            $total += $lineItem->getSubtotal();
        }

        return $total;
    }

    /**
     * @param (\Closure():?LineItem) $resolver
     */
    public function setHighestSubtotalLineItemResolver(\Closure $resolver): static
    {
        $this->highestSubtotalLineItemResolver = $resolver;

        return $this;
    }

    /**
     * @param (\Closure(AlcoholType):list<LineItem>) $resolver
     */
    public function setLineItemsByAlcoholTypeResolver(\Closure $resolver): static
    {
        $this->lineItemsByAlcoholTypeResolver = $resolver;

        return $this;
    }
}
