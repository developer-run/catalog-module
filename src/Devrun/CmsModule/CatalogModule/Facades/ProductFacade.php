<?php
/**
 * This file is part of nivea-2019-klub-rewards.
 * Copyright (c) 2019
 *
 * @file    ProductFacade.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CmsModule\CatalogModule\Facades;


use Devrun\CatalogModule\Entities\FeedLog;
use Devrun\CatalogModule\Entities\ProductEntity;
use Devrun\CatalogModule\Repositories\ProductRepository;

class ProductFacade
{

    /** @var ProductRepository */
    private $productRepository;

    /**
     * ProductFacade constructor.
     *
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }


    public function createLog(ProductEntity $productEntity, string $text)
    {
        $feedLog = new FeedLog($productEntity, $text);
        $this->productRepository->getEntityManager()->persist($feedLog);
    }










}