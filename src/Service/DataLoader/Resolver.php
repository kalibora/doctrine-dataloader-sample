<?php

namespace App\Service\DataLoader;

use App\Service\DataLoader\Data\Cache;
use App\Service\DataLoader\Data\TargetIdSet;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Contracts\Service\ResetInterface;

/**
 * エンティティの情報を一括で取得し、それを解決するサービス
 *
 * GraphQL の DataLoader 的な役割を果たすことを想定している
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
     * 情報を一括取得する対象のエンティティとIDを追加する
     *
     * @param class-string $entity エンティティのクラス名
     */
    public function addId(string $entity, int $id): void
    {
        $this->targetIdSet->add($entity, $id);
    }

    /**
     * 対象のIDとキャッシュをクリアする
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
     * Loaderのクラス名、ID、引数を指定してキャッシュまたはDBから一括取得したデータを元に値を解決して返す
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

                // 返却されなかったIDも null でキャッシュしておくことで再度のロードを防ぐ
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
