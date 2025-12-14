<?php

namespace App\Service\DataLoader\Loader;

use App\Service\DataLoader\ArgsInterface;

final class NoArgs implements ArgsInterface
{
    public static function fromArray(array $args): self
    {
        return new self();
    }

    public function toCacheKey(): string
    {
        return '';
    }
}
