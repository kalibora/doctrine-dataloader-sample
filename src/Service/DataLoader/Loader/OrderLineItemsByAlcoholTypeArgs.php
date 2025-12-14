<?php

namespace App\Service\DataLoader\Loader;

use App\Entity\AlcoholType;
use App\Service\DataLoader\ArgsInterface;

final class OrderLineItemsByAlcoholTypeArgs implements ArgsInterface
{
    public function __construct(
        private readonly AlcoholType $alcoholType,
    ) {
    }

    public function getAlcoholType(): AlcoholType
    {
        return $this->alcoholType;
    }

    public static function fromArray(array $args): self
    {
        $alcoholType = $args[0] ?? null;

        if (!$alcoholType instanceof AlcoholType) {
            throw new \InvalidArgumentException('Invalid alcohol type argument');
        }

        return new self($alcoholType);
    }

    public function toCacheKey(): string
    {
        return $this->alcoholType->value;
    }
}
