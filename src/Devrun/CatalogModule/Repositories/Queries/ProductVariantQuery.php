<?php
/**
 * This file is part of affiliate-bagin.cz.
 * Copyright (c) 2019
 *
 * @file    ProductVariantQuery.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Repositories\Queries;

use Closure;
use Devrun\CatalogModule\Entities\ProductEntity;
use Devrun\CatalogModule\Entities\ProductVariantEntity;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Kdyby;
use Kdyby\Doctrine\QueryObject;
use Kdyby\Persistence\Queryable;

class ProductVariantQuery extends QueryObject
{

    /**
     * @var array|Closure[]
     */
    private $filter = [];

    /**
     * @var array|Closure[]
     */
    private $select = [];



    public function withProductsCount(array $variantIDS = [], $priceMin = null, $priceMax = null)
    {
        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($variantIDS, $priceMin, $priceMax) {

            $subDQL = $qb->getEntityManager()->createQueryBuilder()
                ->from(ProductEntity::class, 'p1')
                ->select('count(p1)')
//                ->addSelect('case when p1.id > 60000 then count(p1) else true end')
                ->leftJoin('p1.variants', 'v')
                ->join('p1.categories', 'c1')

                ->where('v = q')
                ->andWhere('c1 = :category')
//                ->andWhere("CASE WHEN v.id > 0 THEN v.name ELSE true END = 'Barva'")


//                ->andWhere('v.id = :variantID')
//                ->andWhere('v.id IN (:variantID)')
//                ->andWhere('v.name = :variantName')
//                ->andWhere('v.name IN (:variantName)')
//                ->andWhere('v.name = :variantName OR v.id = :variantID')
//                ->orWhere('v.id = :variantID')
            ;

//            $qb->setParameter('variantName', 'Barva');
//            $qb->setParameter('variantName', ['Barva', 'Materiál']);
//            $qb->setParameter('variantID', 53);

            if ($priceMin) {
                $subDQL->andWhere('p1.price >= :priceMin');
                $qb->setParameter('priceMin', $priceMin);
            }
            if ($priceMax) {
                $subDQL->andWhere('p1.price <= :priceMax');
                $qb->setParameter('priceMax', $priceMax);
            }

            /** @var array $testVariants */
            $testVariants = [1, 53, 51];

            $parameters = [];
            foreach ($variantIDS as $idx => $variantID) {
                $subDQL
                    ->join('p1.variants', "vv$idx")
                    ->andWhere("vv$idx = $variantID")
//                    ->andWhere("vv$idx = v")
//                    ->andWhere("(CASE WHEN v.name = vv$idx.name  THEN v ELSE $variantID END) = vv$idx")


//                    ->andWhere("vv$idx.name = :var_$idx")
//                    ->andWhere("vv$idx = $variantID AND vv$idx.name = v.name")
//                    ->andWhere("vv$idx = $variantID OR vv$idx.name = :var_$idx")
//                    ->andWhere("vv$idx.name = :var_$idx OR vv$idx.id = :varID_$idx")

//                    ->setParameter("var_$idx", $variantID)
//                    ->setParameter("var1_$idx", $variantID)

//                    ->andWhere("vv$idx IN (:variant_$idx)")
                ;
//                $qb->andWhere("v$idx IN (:variant_$idx)")->setParameter("variant_$idx", $variantID);
//                $qb->setParameter("var_$idx", $variantIDS);

//                $qb->setParameter("variant_$idx", $testVariants);

//                $qb->setParameter("var_$idx", 'Barva');
//                $qb->setParameter("varID_$idx", 53);

                if ($idx == 0) {
//                    $qb->setParameter("var_$idx", 'Materiál');

                } elseif ($idx == 1) {
//                    $qb->setParameter("var_$idx", 'Rozměry');
//                    $qb->setParameter("var_$idx", 'Materiál');

                }


//                $qb->setParameter("var_$idx", ['Materiál', 'Rozměry']);

//                $parameters["var_$idx"] = $variantID;
                $parameters["variant_$idx"] = $variantID;
            }
//            $subDQL->setParameters($parameters);

            $qb->addSelect("({$subDQL->getDQL()}) as products")
//                ->setParameters($parameters)
            ;
        };

        return $this;
    }






    public function inProductCategory($category)
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($category) {
            $qb->join('q.products', 'p')
               ->join('p.categories', 'c');

            $qb->andWhere('c = :category')->setParameter('category', $category);
        };
        return $this;
    }





    /**
     * @param Queryable $repository
     *
     * @return Query|QueryBuilder
     */
    protected function doCreateQuery(Queryable $repository)
    {
        $qb = $this->createBasicDql($repository);
        foreach ($this->select as $modifier) {
            $modifier($qb);
        }

        return $qb;

    }


    private function createBasicDql(Queryable $repository)
    {
        $qb = $repository->createQueryBuilder()
            ->select('q as entity')->from(ProductVariantEntity::getClassName(), 'q');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }


}