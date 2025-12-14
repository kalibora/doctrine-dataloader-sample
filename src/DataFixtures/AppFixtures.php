<?php

namespace App\DataFixtures;

use App\Entity\AlcoholType;
use App\Entity\Order;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    /**
     * @var array<string, list<string>>
     */
    private array $productNamesByType = [];

    /**
     * @var list<string>
     */
    private array $allProductNames = [];

    public function load(ObjectManager $manager): void
    {
        $this->loadProducts($manager);
        $this->loadOrders($manager);

        $manager->flush();
    }

    private function loadProducts(ObjectManager $manager): void
    {
        // 日本で実際に流通している銘柄をざっくり100件用意（価格は概算のテスト用）
        $data = [
            // BEER / RTD
            [AlcoholType::BEER, 'アサヒ スーパードライ', 280],
            [AlcoholType::BEER, 'キリン 一番搾り', 280],
            [AlcoholType::BEER, 'サッポロ 黒ラベル', 280],
            [AlcoholType::BEER, 'ヱビス ビール', 320],
            [AlcoholType::BEER, 'ザ・プレミアム・モルツ', 330],
            [AlcoholType::BEER, 'オリオン ザ・ドラフト', 260],
            [AlcoholType::BEER, 'アサヒ クリアアサヒ', 220],
            [AlcoholType::BEER, 'キリン のどごし〈生〉', 220],
            [AlcoholType::BEER, 'サッポロ ゴールドスター', 230],
            [AlcoholType::BEER, 'よなよなエール', 350],
            [AlcoholType::BEER, 'インドの青鬼', 360],
            [AlcoholType::BEER, '常陸野ネスト ホワイトエール', 400],
            [AlcoholType::BEER, 'コエド 瑠璃', 380],
            [AlcoholType::BEER, 'コエド 伽羅', 380],
            [AlcoholType::BEER, 'アサヒ レッドアイ', 300],
            [AlcoholType::BEER, 'サントリー 金麦', 220],
            [AlcoholType::BEER, 'こだわり酒場のレモンサワー缶', 200],
            [AlcoholType::BEER, 'ストロングゼロ ダブルレモン', 200],
            [AlcoholType::BEER, 'ストロングゼロ ダブルグレフル', 200],
            [AlcoholType::BEER, 'ほろよい 白いサワー', 170],
            [AlcoholType::BEER, 'ほろよい もも', 170],
            [AlcoholType::BEER, 'ほろよい ハピクルサワー', 170],
            [AlcoholType::BEER, '氷結 シチリア産レモン', 190],
            [AlcoholType::BEER, '氷結 グレープフルーツ', 190],
            [AlcoholType::BEER, '氷結 無糖レモン', 190],
            [AlcoholType::BEER, '本搾り レモン', 210],
            [AlcoholType::BEER, 'サッポロ 99.99 クリアレモン', 200],
            [AlcoholType::BEER, 'サッポロ 99.99 クリアグレフル', 200],
            [AlcoholType::BEER, '宝 焼酎ハイボール ドライ', 180],
            [AlcoholType::BEER, '極上レモンサワー 丸おろしレモン', 180],

            // WINE（国産を中心に）
            [AlcoholType::WINE, 'シャトー・メルシャン 桔梗ヶ原メルロー', 4500],
            [AlcoholType::WINE, 'シャトー・メルシャン 椀子シラー', 4000],
            [AlcoholType::WINE, 'グレイス甲州', 2500],
            [AlcoholType::WINE, '登美の丘 赤', 6000],
            [AlcoholType::WINE, 'おたる ナイアガラ 白', 1800],

            // SAKE（日本酒）
            [AlcoholType::SAKE, '獺祭 純米大吟醸45', 3500],
            [AlcoholType::SAKE, '獺祭 純米大吟醸23', 9000],
            [AlcoholType::SAKE, '久保田 万寿', 8500],
            [AlcoholType::SAKE, '久保田 千寿', 2500],
            [AlcoholType::SAKE, '八海山 特別純米', 2600],
            [AlcoholType::SAKE, '菊水 純米吟醸', 1800],
            [AlcoholType::SAKE, '十四代 本丸', 12000],
            [AlcoholType::SAKE, '醸し人九平次 彼の地', 4000],
            [AlcoholType::SAKE, '浦霞 禅 吟醸', 3000],
            [AlcoholType::SAKE, '天狗舞 山廃純米', 2000],
            [AlcoholType::SAKE, '出羽桜 桜花 吟醸', 1900],
            [AlcoholType::SAKE, '梵 GOLD 純米大吟醸', 3500],
            [AlcoholType::SAKE, '飛露喜 特別純米', 3200],
            [AlcoholType::SAKE, '田酒 特別純米', 3100],
            [AlcoholType::SAKE, '而今 純米吟醸', 4200],
            [AlcoholType::SAKE, '南部美人 純米吟醸', 2400],
            [AlcoholType::SAKE, '赤武 純米吟醸', 2600],
            [AlcoholType::SAKE, '磯自慢 純米吟醸', 4500],
            [AlcoholType::SAKE, '豊盃 純米吟醸', 2800],
            [AlcoholType::SAKE, '東洋美人 壱歩 亀治好日', 3000],
            [AlcoholType::SAKE, '仙禽 オーガニック ナチュール', 2800],
            [AlcoholType::SAKE, '風の森 ALPHA', 2700],
            [AlcoholType::SAKE, '鍋島 純米大吟醸', 5000],
            [AlcoholType::SAKE, '澤屋まつもと 守破離', 2300],
            [AlcoholType::SAKE, '黒龍 しずく', 13000],
            [AlcoholType::SAKE, '天美 純米吟醸', 2700],
            [AlcoholType::SAKE, '高山 純米大吟醸', 3000],
            [AlcoholType::SAKE, '花陽浴 純米吟醸', 3300],
            [AlcoholType::SAKE, '新政 No.6 R-type', 5000],
            [AlcoholType::SAKE, '獺祭 スパークリング45', 3200],

            // WHISKY
            [AlcoholType::WHISKY, '山崎 12年', 11000],
            [AlcoholType::WHISKY, '山崎 18年', 60000],
            [AlcoholType::WHISKY, '白州 12年', 12000],
            [AlcoholType::WHISKY, '響 JAPANESE HARMONY', 9000],
            [AlcoholType::WHISKY, '響 21年', 48000],
            [AlcoholType::WHISKY, '竹鶴 ピュアモルト', 5000],
            [AlcoholType::WHISKY, 'ニッカ カフェグレーン', 4500],
            [AlcoholType::WHISKY, 'ニッカ フロム・ザ・バレル', 4800],
            [AlcoholType::WHISKY, '余市 シングルモルト', 6200],
            [AlcoholType::WHISKY, '宮城峡 シングルモルト', 5200],
            [AlcoholType::WHISKY, 'マルス 信州 シングルモルト', 6000],
            [AlcoholType::WHISKY, 'マルス 越百 モルトセレクション', 5500],
            [AlcoholType::WHISKY, 'イチローズモルト 秩父 THE FIRST TEN', 15000],
            [AlcoholType::WHISKY, '富士 シングルグレーン', 6000],
            [AlcoholType::WHISKY, 'サントリー 知多', 4000],
            [AlcoholType::WHISKY, 'ブラックニッカ クリア', 1300],
            [AlcoholType::WHISKY, 'ブラックニッカ ディープブレンド', 1800],
            [AlcoholType::WHISKY, 'サントリー トリス クラシック', 1200],
            [AlcoholType::WHISKY, 'サントリー TOKI', 3200],
            [AlcoholType::WHISKY, '嘉之助 シングルモルト', 7200],

            // SHOCHU / UMESHU (type: SAKE for分類簡略化)
            [AlcoholType::SAKE, '黒霧島', 900],
            [AlcoholType::SAKE, '赤霧島', 1100],
            [AlcoholType::SAKE, '魔王', 3500],
            [AlcoholType::SAKE, '村尾', 4500],
            [AlcoholType::SAKE, '森伊蔵', 5000],
            [AlcoholType::SAKE, '富乃宝山', 1800],
            [AlcoholType::SAKE, '吉兆宝山', 1900],
            [AlcoholType::SAKE, '三岳', 1700],
            [AlcoholType::SAKE, 'いいちこ 日田全麹', 1000],
            [AlcoholType::SAKE, '鳥飼', 2000],
            [AlcoholType::SAKE, '鍛高譚 しそ焼酎', 1200],
            [AlcoholType::SAKE, '雲海 そば焼酎', 1100],
            [AlcoholType::SAKE, '菊之露 ブラウン', 1200],
            [AlcoholType::SAKE, '瑞泉 おもろ 15年', 4500],
            [AlcoholType::SAKE, '梅乃宿 あらごし梅酒', 1500],
            [AlcoholType::SAKE, 'チョーヤ 梅酒 紀州', 1200],
            [AlcoholType::SAKE, 'チョーヤ 梅酒 ブラック', 1300],
            [AlcoholType::SAKE, '山崎蒸溜所貯蔵 焙煎樽仕込梅酒', 1600],
            [AlcoholType::SAKE, '紀州 南高梅 原酒', 2000],
            [AlcoholType::SAKE, '中野BC 蜜柑梅酒', 1500],
        ];

        foreach ($data as [$alcoholType, $name, $price]) {
            $product = new Product(
                name: $name,
                price: $price,
                alcoholType: $alcoholType,
            );
            $manager->persist($product);

            $this->addReference($name, $product);

            $this->productNamesByType[$alcoholType->value][] = $name;
            $this->allProductNames[] = $name;
        }
    }

    private function loadOrders(ObjectManager $manager): void
    {
        $orders = [
            // ビール党
            ['2025-12-01 12:00:00', AlcoholType::BEER, 400],
            // ワイン好き
            ['2025-12-02 13:30:00', AlcoholType::WINE, 300],
            // ウイスキー好き
            ['2025-12-03 18:45:00', AlcoholType::WHISKY, 500],
            // 日本酒好き（焼酎・梅酒含む）
            ['2025-12-04 20:15:00', AlcoholType::SAKE, 500],
            // なんでも好きな大酒飲み
            ['2025-12-05 19:00:00', null, 900],
        ];

        foreach ($orders as [$orderedAt, $primaryType, $lines]) {
            $order = new Order(
                orderedAt: new \DateTimeImmutable($orderedAt),
            );

            $primaryPool = $primaryType
                ? ($this->productNamesByType[$primaryType->value] ?? [])
                : $this->allProductNames;

            $secondaryPool = $this->allProductNames;

            $this->generateLineItems(
                order: $order,
                primaryPool: $primaryPool,
                secondaryPool: $secondaryPool,
                lines: $lines,
            );

            $manager->persist($order);
        }
    }

    /**
     * テーマに応じて明細を生成（70%メインカテゴリ、30%その他）。重複明細も許容し、量の多い注文を再現。
     * ランダムを使わずインデックス計算で決定するので、毎回同じ結果になります。
     *
     * @param list<string> $primaryPool
     * @param list<string> $secondaryPool
     */
    private function generateLineItems(
        Order $order,
        array $primaryPool,
        array $secondaryPool,
        int $lines,
    ): void {
        if (empty($primaryPool)) {
            // 念のため、メインカテゴリが空なら全商品から選ぶ
            $primaryPool = $secondaryPool;
        }

        $primaryCount = \count($primaryPool);
        $secondaryCount = \count($secondaryPool);

        for ($i = 0; $i < $lines; ++$i) {
            $usePrimary = ($i % 10) < 7; // 70% をメインカテゴリに固定
            $productName = $usePrimary
                ? $primaryPool[$i % $primaryCount]
                : $secondaryPool[$i % $secondaryCount];

            // 数量も決定的に：1〜12 を繰り返し
            $quantity = ($i % 12) + 1;

            /** @var Product $product */
            $product = $this->getReference($productName, Product::class);
            $order->addProduct($product, $quantity);
        }
    }
}
