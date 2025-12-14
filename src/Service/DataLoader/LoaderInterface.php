<?php

namespace App\Service\DataLoader;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * 情報を一括で取得するためのローダーインターフェース
 *
 * @template TA of ArgsInterface = ArgsInterface
 * @template TR of mixed = mixed
 */
#[AutoconfigureTag('app.data_loader')]
interface LoaderInterface
{
    /**
     * サポートしているエンティティクラスを返す
     *
     * @return class-string
     */
    public static function getSupportedEntity(): string;

    /**
     * 引数オブジェクトを生成して返す
     *
     * @param list<mixed> $rawArgs
     *
     * @return TA
     */
    public function createArgs(array $rawArgs): ArgsInterface;

    /**
     * 指定したID群に対応する情報を一括で取得して返す
     *
     * @param list<int> $ids
     * @param TA        $args
     *
     * @return array<int, TR> id をキーとした取得結果のマップ
     */
    public function load(array $ids, ArgsInterface $args): array;
}
