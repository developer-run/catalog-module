<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    CatalogQuery.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Repositories\Queries;


use Devrun\CatalogModule\Entities\ProductEntity;
use Devrun\CatalogModule\Repositories\Filters\CatalogFilter;
use Kdyby;
use Kdyby\Doctrine\QueryObject;
use Tracy\Debugger;

class ProductQuery extends QueryObject
{
    /**
     * @var array|\Closure[]
     */
    private $filter = [];

    /**
     * @var array|\Closure[]
     */
    private $select = [];



    public function filtered(CatalogFilter $filter)
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($filter) {

            if ($priceMin = $filter->getPriceMin()) {
                $qb->andWhere('q.price >= :priceMin')->setParameter('priceMin', $priceMin);
            }

            if ($priceMax = $filter->getPriceMax()) {
                $qb->andWhere('q.price <= :priceMax')->setParameter('priceMax', $priceMax);
            }

            if ($category = $filter->getCategory()) {
                $qb->leftJoin('q.categories', 'c');
                $qb->andWhere('c = :category')->setParameter('category', $category);
            }

            if ($term = $filter->getSearchTerm()) {
                $qb->leftJoin('q.translations', 'translations');
                $qb
                    ->andWhere('translations.description LIKE :term OR translations.shortDescription LIKE :term OR translations.title LIKE :term OR translations.name LIKE :term')
                    ->setParameter('term', "%$term%");
            }

            if ($variants = $filter->getVariants()) {
                foreach ($variants as $idx => $variant) {
                    $qb->join('q.variants', "v$idx");
                    $qb->andWhere("v$idx IN (:variant_$idx)")->setParameter("variant_$idx", $variant);
                }
            }

            switch ($filter->getSort()) {
                case $filter::SORT_BY_CHEAPEST:
                    $qb->orderBy('q.price', 'ASC');
                    break;

                case $filter::SORT_BY_DEAREST:
                    $qb->orderBy('q.price', 'DESC');
                    break;

                default:
                    $qb->orderBy('q.inserted', 'DESC');
            }

        };

        return $this;
    }



    public function inCategory($categories)
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($categories) {
            $qb->andWhere('q.categories = :categories')->setParameter('categories', $categories);
        };
        return $this;
    }

    /**
     * @return $this
     */
    public function withCategories(): ProductQuery
    {
        $this->onPostFetch[] = function (QueryObject $_, Kdyby\Persistence\Queryable $repository, \Iterator $iterator) {
            $ids = iterator_to_array($iterator);

            $query = $repository->createQueryBuilder()
                ->select('PARTIAL q.{id}, categories, categoriesTranslations')
                ->from(ProductEntity::getClassName(), 'q')
                ->leftJoin('q.categories', 'categories')
                ->leftJoin('categories.translations', 'categoriesTranslations')
                ->andWhere('q.id IN (:products)')->setParameter('products', $ids)
                ->getQuery();

            return $query->setCacheable(false)
                ->getResult();
        };

        return $this;
    }


    public function withImages(): ProductQuery
    {
        $this->onPostFetch[] = function (QueryObject $_, Kdyby\Persistence\Queryable $repository, \Iterator $iterator) {
            $ids = iterator_to_array($iterator);

            $repository->createQueryBuilder()
                ->select('PARTIAL q.{id}, images')
                ->from(ProductEntity::getClassName(), 'q')
                ->leftJoin('q.images', 'images')
                ->andWhere('q.id IN (:products)')->setParameter('products', $ids)
                ->getQuery()->getResult();
        };

        return $this;
    }


    public function withTranslations(): ProductQuery
    {
        $this->onPostFetch[] = function (QueryObject $_, Kdyby\Persistence\Queryable $repository, \Iterator $iterator) {
            $ids = iterator_to_array($iterator);

            $repository->createQueryBuilder()
                ->select('PARTIAL q.{id}, translations')
                ->from(ProductEntity::getClassName(), 'q')
                ->leftJoin('q.translations', 'translations')
                ->andWhere('q.id IN (:products)')->setParameter('products', $ids)
                ->getQuery()->getResult();
        };

        return $this;
    }


    public function withVariants(): ProductQuery
    {
        $this->onPostFetch[] = function (QueryObject $_, Kdyby\Persistence\Queryable $repository, \Iterator $iterator) {
            /** @var ProductEntity[] $ids */
            $ids = iterator_to_array($iterator);

            $repository->createQueryBuilder()
                ->select('PARTIAL q.{id}, variants')
                ->from(ProductEntity::getClassName(), 'q')
                ->leftJoin('q.variants', 'variants')
                ->andWhere('q.id IN (:products)')->setParameter('products', $ids)
                ->getQuery()->getResult();
        };

        return $this;
    }



    /**
     * pravidlo, neselectovat toMany, můžete filtrovat toMany ,  toMany až v dalším kroku, až jsou vyřešeny toOne
     *
     * @param \Kdyby\Persistence\Queryable $repository
     *
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(Kdyby\Persistence\Queryable $repository)
    {
        $qb = $this->createBasicDql($repository);
        foreach ($this->select as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }


    private function createBasicDql(Kdyby\Persistence\Queryable $repository)
    {
        $qb = $repository->createQueryBuilder()
            ->select('q')->from(ProductEntity::getClassName(), 'q');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

}