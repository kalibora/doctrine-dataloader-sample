<?php

namespace App\Service\DataLoader\Data;

/**
 * Target IDs to batch-load per entity.
 */
final class TargetIdSet
{
    /**
     * @var array<class-string, array<int, true>>
     */
    private array $data = [];

    public function __construct(
    ) {
    }

    /**
     * @param class-string $entity
     */
    public function add(string $entity, int $id): void
    {
        $this->data[$entity][$id] = true;
    }

    /**
     * @param class-string $entity
     *
     * @return list<int>
     */
    public function getIds(string $entity): array
    {
        if (!isset($this->data[$entity])) {
            return [];
        }

        return array_keys($this->data[$entity]);
    }

    public function clear(): void
    {
        $this->data = [];
    }
}
