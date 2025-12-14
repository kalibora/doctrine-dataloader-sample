<?php

namespace App\Service\DataLoader;

/**
 * 情報を一括で取得する際の引数のインターフェース
 */
interface ArgsInterface
{
    /**
     * 引数の配列からインスタンスを生成する
     *
     * @param list<mixed> $args
     */
    public static function fromArray(array $args): self;

    /**
     * 引数に応じたキャッシュキーを生成する
     */
    public function toCacheKey(): string;
}
