<?php

namespace App\Service\DataLoader\Data;

use App\Service\DataLoader\ArgsInterface;
use App\Service\DataLoader\LoaderInterface;

/**
 * LoaderInterface、引数、エンティティのIDごとのキャッシュ
 */
final class Cache
{
    /**
     * @var array<string, array<string, array<int, mixed>>>
     */
    private array $data = [];

    public function __construct(
    ) {
    }

    public function add(LoaderInterface $loader, ArgsInterface $args, int $id, mixed $value): void
    {
        $this->data[$loader::class][$args->toCacheKey()][$id] = $value;
    }

    public function has(LoaderInterface $loader, ArgsInterface $args, int $id): bool
    {
        return array_key_exists($id, $this->data[$loader::class][$args->toCacheKey()] ?? []);
    }

    public function get(LoaderInterface $loader, ArgsInterface $args, int $id): mixed
    {
        return $this->data[$loader::class][$args->toCacheKey()][$id] ?? null;
    }

    /**
     * @return list<int>
     */
    public function getIds(LoaderInterface $loader, ArgsInterface $args): array
    {
        return array_keys($this->data[$loader::class][$args->toCacheKey()] ?? []);
    }

    public function clear(): void
    {
        $this->data = [];
    }
}
