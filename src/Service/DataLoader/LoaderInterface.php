<?php

namespace App\Service\DataLoader;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Loader interface for batch-fetching data.
 *
 * @template TA of ArgsInterface = ArgsInterface
 * @template TR of mixed = mixed
 */
#[AutoconfigureTag('app.data_loader')]
interface LoaderInterface
{
    /**
     * Return the supported entity class.
     *
     * @return class-string
     */
    public static function getSupportedEntity(): string;

    /**
     * Create and return the args object.
     *
     * @param list<mixed> $rawArgs
     *
     * @return TA
     */
    public function createArgs(array $rawArgs): ArgsInterface;

    /**
     * Batch-load data for the specified IDs.
     *
     * @param list<int> $ids
     * @param TA        $args
     *
     * @return array<int, TR> Map of results keyed by id.
     */
    public function load(array $ids, ArgsInterface $args): array;
}
