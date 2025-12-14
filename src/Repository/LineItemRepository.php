<?php

namespace App\Repository;

use App\Entity\AlcoholType;
use App\Entity\LineItem;
use App\Entity\Order;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LineItem>
 */
class LineItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LineItem::class);
    }

    /**
     * 引数に指定されたorderIdごとに、小計が最大のLineItemをそれぞれ1件ずつ取得する
     *
     * @param list<int> $orderIds
     *
     * @return array<int, LineItem> キーがorderId、値がLineItemの連想配列
     */
    public function findHighestSubtotalLineItemMap(array $orderIds): array
    {
        if (0 === count($orderIds)) {
            return [];
        }

        // CTE + ウィンドウ関数で top1 を決定し、そのまま LineItem / Order / Product を1クエリで取得
        $em = $this->getEntityManager();

        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata(
            LineItem::class,
            alias: 'li',
            renamedColumns: ['id' => 'li_id'],
        );
        $rsm->addJoinedEntityFromClassMetadata(
            Order::class,
            alias: 'o',
            parentAlias: 'li',
            relation: 'order',
            renamedColumns: ['id' => 'o_id'],
        );
        $rsm->addJoinedEntityFromClassMetadata(
            Product::class,
            alias: 'p',
            parentAlias: 'li',
            relation: 'product',
            renamedColumns: ['id' => 'p_id'],
        );

        $orderJoinColumnName = $em->getClassMetadata(LineItem::class)->getSingleAssociationJoinColumnName('order');
        $rsm->addIndexBy('li', $orderJoinColumnName); // 戻り値は orderId をキーにする

        $select = $rsm->generateSelectClause();

        $sql = <<<SQL
WITH ranked AS (
    SELECT
        li.id,
        ROW_NUMBER() OVER (PARTITION BY li.order_id ORDER BY li.sub_total DESC, li.id ASC) AS rn
    FROM line_item li
    WHERE li.order_id IN (:orderIds)
)
SELECT
    $select
FROM ranked r
    INNER JOIN line_item li ON li.id = r.id
    INNER JOIN `order` o ON o.id = li.order_id
    INNER JOIN product p ON p.id = li.product_id
WHERE r.rn = 1
ORDER BY li.order_id ASC
SQL;

        $query = $em->createNativeQuery($sql, $rsm)
            ->setParameter('orderIds', $orderIds, ArrayParameterType::INTEGER);

        return $query->getResult();
    }

    /**
     * 引数に指定されたorderIdごとに、指定されたアルコール種別のLineItemをすべて取得する
     *
     * @param list<int> $orderIds
     *
     * @return array<int, list<LineItem>> キーがorderId、値がLineItemの配列の連想配列
     */
    public function findLineItemsByAlcoholType(AlcoholType $alcoholType, array $orderIds): array
    {
        if (0 === count($orderIds)) {
            return [];
        }

        $lineItems = $this->createQueryBuilder('li')
            ->innerJoin('li.order', 'o')->addSelect('o')
            ->innerJoin('li.product', 'p')->addSelect('p')
            ->where('o.id IN (:orderIds)')
            ->andWhere('p.alcoholType = :alcoholType')
            ->setParameter('orderIds', $orderIds)
            ->setParameter('alcoholType', $alcoholType)
            ->orderBy('o.id', 'ASC')
            ->addOrderBy('li.lineNumber', 'ASC')
            ->getQuery()
            ->getResult();

        $map = [];
        foreach ($lineItems as $lineItem) {
            $orderId = $lineItem->getOrder()->getId();

            $map[$orderId] ??= [];
            $map[$orderId][] = $lineItem;
        }

        return $map;
    }
}
