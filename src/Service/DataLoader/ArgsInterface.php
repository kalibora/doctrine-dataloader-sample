<?php

namespace App\Service\DataLoader;

/**
 * Interface for args used in batch-loading data.
 */
interface ArgsInterface
{
    /**
     * Create an instance from an args array.
     *
     * @param list<mixed> $args
     */
    public static function fromArray(array $args): self;

    /**
     * Generate a cache key for these args.
     */
    public function toCacheKey(): string;
}
