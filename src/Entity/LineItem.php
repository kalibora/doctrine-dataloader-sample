<?php

namespace App\Entity;

use App\Repository\LineItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LineItemRepository::class)]
#[ORM\Index(name: 'order_sub_total_idx', columns: ['order_id', 'sub_total'])]
class LineItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lineItems')]
    #[ORM\JoinColumn(nullable: false)]
    private Order $order;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    #[ORM\Column]
    private int $lineNumber;

    #[ORM\Column]
    private int $quantity;

    #[ORM\Column]
    private int $unitPrice;

    /**
     * Derived field stored to enable indexing.
     */
    #[ORM\Column]
    private int $subTotal;

    public function __construct(
        Order $order,
        Product $product,
        int $lineNumber,
        int $quantity,
        int $unitPrice,
    ) {
        $this->order = $order;
        $this->product = $product;
        $this->lineNumber = $lineNumber;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->subTotal = self::calculateSubtotal($quantity, $unitPrice);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): int
    {
        return $this->unitPrice;
    }

    public function getSubtotal(): int
    {
        return $this->subTotal;
    }

    private static function calculateSubtotal(int $quantity, int $unitPrice): int
    {
        return $quantity * $unitPrice;
    }
}
