<?php
/**
 * This file is part of affiliate-bagin.cz.
 * Copyright (c) 2019
 *
 * @file    CarouselProductsControl.php4
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Controls;

use Devrun\Application\UI\Control\Control;
use Devrun\Application\UI\Presenter\TImgStoragePipe;
use Devrun\CatalogModule\Entities\ProductEntity;
use Devrun\CatalogModule\Repositories\ProductRepository;
use Nette\Application\UI\Multiplier;

interface ICarouselProductsControlFactory
{
    /** @return CarouselProductsControl */
    function create();
}

class CarouselProductsControl extends Control
{
    use TImgStoragePipe;

    /** @var ProductRepository @inject */
    public $productRepository;


    /** @var ProductEntity[] */
    private $products = [];

    /** @var array */
    private $criteria = [];


    public function render($params = [])
    {
        $this->criteria = $params['criteria'] ?? [];
//        $products = $this->productRepository->findBy($this->criteria);

        $products = $this->productRepository->createQueryBuilder('e')
            ->addSelect('c')
            ->addSelect('t')
            ->addSelect('i')
            ->join('e.categories', 'c')
            ->leftJoin('e.translations', 't')
            ->leftJoin('e.images', 'i')
            ->whereCriteria($this->criteria)
            ->getQuery()
            ->getResult();



        $template = $this->getTemplate();
        $template->products = $products;
        $template->render();
    }



    /**
     * @param ProductEntity[] $products
     */
    public function setProducts(array $products)
    {
        $this->products = $products;
    }




}