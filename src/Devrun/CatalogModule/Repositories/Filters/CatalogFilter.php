<?php
/**
 * This file is part of affiliate-bagin.cz.
 * Copyright (c) 2019
 *
 * @file    CatalogFilter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Repositories\Filters;

use Devrun\CatalogModule\Entities\CategoryEntity;
use Devrun\CatalogModule\Entities\ProductVariantEntity;

class CatalogFilter
{

    const SORT_BY_DATE = 'newest';
    const SORT_BY_CHEAPEST = 'cheapest';
    const SORT_BY_DEAREST = 'dearest';

    /** @var integer */
    private $priceMin = 0;

    /** @var integer */
    private $priceMax = 0;

    /** @var CategoryEntity|null */
    private $category;

    /** @var string|null */
    private $searchTerm;

    /** @var ProductVariantEntity[][]|null */
    private $variants;


    /** @var string */
    private $sort = self::SORT_BY_DATE;


    /**
     * @return int
     */
    public function getPriceMin(): int
    {
        return $this->priceMin;
    }

    /**
     * @param int $priceMin
     *
     * @return CatalogFilter
     */
    public function setPriceMin(int $priceMin): CatalogFilter
    {
        $this->priceMin = $priceMin;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriceMax(): int
    {
        return $this->priceMax;
    }

    /**
     * @param int $priceMax
     *
     * @return CatalogFilter
     */
    public function setPriceMax(int $priceMax): CatalogFilter
    {
        $this->priceMax = $priceMax;
        return $this;
    }

    /**
     * @return CategoryEntity
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param CategoryEntity $category
     *
     * @return CatalogFilter
     */
    public function setCategory(CategoryEntity $category): CatalogFilter
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getSearchTerm()
    {
        return $this->searchTerm;
    }

    /**
     * @param null|string $searchTerm
     *
     * @return CatalogFilter
     */
    public function setSearchTerm(string $searchTerm = null): CatalogFilter
    {
        $this->searchTerm = htmlspecialchars($searchTerm);
        return $this;
    }

    /**
     * @return ProductVariantEntity[][]|null
     */
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * @param ProductVariantEntity[][]|null $variants
     *
     * @return CatalogFilter
     */
    public function setVariants(array $variants)
    {
        $this->variants = $variants;
        return $this;
    }

    /**
     * @return string
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param string $sort
     *
     * @return CatalogFilter
     */
    public function setSort(string $sort = null): CatalogFilter
    {
        $this->sort = $sort;
        return $this;
    }


}