<?php

namespace App\Service\DataLoader;

use App\Service\DataLoader\Data\Cache;
use App\Service\DataLoader\Data\TargetIdSet;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Service that batch-loads entity data and resolves it.
 *
 * Intended to play a GraphQL DataLoader-like role.
 */
final class Resolver implements ResetInterface
{
    /**
     * @var array<string, LoaderInterface>
     */
    private readonly array $loaders;

    private TargetIdSet $targetIdSet;

    private Cache $cache;

    /**
     * @param iterable<LoaderInterface> $loaders
     */
    public function __construct(
        #[AutowireIterator('app.data_loader')]
        iterable $loaders,
    ) {
        $fixedLoaders = [];

        foreach ($loaders as $loader) {
            $fixedLoaders[$loader::class] = $loader;
        }

        $this->loaders = $fixedLoaders;

        $this->targetIdSet = new TargetIdSet();
        $this->cache = new Cache();
    }

    /**
     * Add the entity and ID to be batch-loaded.
     *
     * @param class-string $entity Entity class name.
     */
    public function addId(string $entity, int $id): void
    {
        $this->targetIdSet->add($entity, $id);
    }

    /**
     * Clear target IDs and cache.
     */
    public function clear(): void
    {
        $this->targetIdSet->clear();
        $this->cache->clear();
    }

    /**
     * @see ResetInterface
     */
    public function reset(): void
    {
        $this->clear();
    }

    /**
     * Resolve a value by loader class, ID, and args using data batch-loaded from cache or DB.
     *
     * @param class-string<LoaderInterface> $loaderClass
     * @param list<mixed>                   $rawArgs
     */
    public function resolve(string $loaderClass, int $id, array $rawArgs = []): mixed
    {
        $loader = $this->getLoader($loaderClass);
        $entity = $loader::getSupportedEntity();
        $args = $loader->createArgs($rawArgs);

        if (!$this->cache->has($loader, $args, $id)) {
            $this->addId($entity, $id);

            $targetIds = $this->targetIdSet->getIds($entity);
            $cachedIds = $this->cache->getIds($loader, $args);
            $unloadedIds = array_values(array_diff($targetIds, $cachedIds));

            if (count($unloadedIds) > 0) {
                $loadedValues = $loader->load($unloadedIds, $args);

                // Cache missing IDs as null to prevent repeated loads.
                $missingIds = array_diff($unloadedIds, array_keys($loadedValues));

                foreach ($missingIds as $missingId) {
                    $this->cache->add($loader, $args, $missingId, null);
                }

                foreach ($loadedValues as $loadedId => $loadedValue) {
                    $this->cache->add($loader, $args, $loadedId, $loadedValue);
                }
            }
        }

        return $this->cache->get($loader, $args, $id);
    }

    /**
     * @param class-string<LoaderInterface> $loaderClass
     */
    private function getLoader(string $loaderClass): LoaderInterface
    {
        $loader = $this->loaders[$loaderClass] ?? null;

        if (null === $loader) {
            throw new \InvalidArgumentException(sprintf('Loader "%s" is not registered.', $loaderClass));
        }

        return $loader;
    }
}
