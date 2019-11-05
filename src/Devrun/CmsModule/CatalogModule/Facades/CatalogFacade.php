<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    CatalogFacade.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CmsModule\CatalogModule\Facades;

use Devrun\CatalogModule\Controls\ICarouselProductsControlFactory;
use Devrun\CatalogModule\Controls\ICatalogControlFactory;
use Devrun\CatalogModule\Controls\ICategoryTreeControlFactory;
use Devrun\CatalogModule\Entities\FeedLog;
use Devrun\CatalogModule\Entities\ProductEntity;
use Devrun\CatalogModule\Repositories\CategoryImageRepository;
use Devrun\CatalogModule\Repositories\CategoryRepository;
use Devrun\CatalogModule\Repositories\OrderRepository;
use Devrun\CatalogModule\Repositories\ProductImageRepository;
use Devrun\CatalogModule\Repositories\ProductRepository;
use Devrun\CatalogModule\Repositories\ProductVariantRepository;
use Devrun\CmsModule\CatalogModule\Forms\ICategoryFormFactory;
use Devrun\CmsModule\CatalogModule\Forms\IImageFormFactory;
use Devrun\CmsModule\CatalogModule\Forms\IImagesFormFactory;
use Devrun\CmsModule\CatalogModule\Forms\IProductFormFactory;
use Devrun\CmsModule\Controls\ICarouselItemsControlFactory;
use Kdyby\Doctrine\EntityManager;

class CatalogFacade
{
    /** @var ICategoryFormFactory @inject */
    public $categoryFormFactory;

    /** @var IProductFormFactory @inject */
    public $productFormFactory;

    /** @var IImagesFormFactory @inject */
    public $imagesFormFactory;

    /** @var IImageFormFactory @inject */
    public $imageFormFactory;

    /** @var TrackingFacade @inject */
    public $trackingFacade;


    /** @var ICategoryTreeControlFactory @inject */
    public $categoryTreeControlFactory;



    /** @var ICatalogControlFactory @inject */
    public $catalogControlFactory;

    /** @var ICarouselProductsControlFactory @inject */
    public $carouselProductsControlFactory;

    /** @var ICarouselItemsControlFactory @inject */
    public $carouselItemsControlFactory;



    /** @var EntityManager */
    private $entityManager;

    /** @var CategoryRepository */
    private $categoryRepository;

    /** @var ProductVariantRepository */
    private $productVariantRepository;

    /** @var CategoryImageRepository */
    private $categoryImageRepository;

    /** @var ProductRepository */
    private $productRepository;

    /** @var OrderRepository */
    private $orderRepository;

    /** @var ProductImageRepository */
    private $productImageRepository;

    /**
     * CatalogFacade constructor.
     *
     * @param CategoryRepository       $categoryRepository
     * @param CategoryImageRepository  $categoryImageRepository
     * @param ProductRepository        $productRepository
     * @param OrderRepository          $orderRepository
     * @param ProductVariantRepository $productVariantRepository
     * @param ProductImageRepository   $imageRepository
     */
    public function __construct(CategoryRepository $categoryRepository, CategoryImageRepository $categoryImageRepository, ProductRepository $productRepository,
                                OrderRepository $orderRepository, ProductVariantRepository $productVariantRepository, ProductImageRepository $imageRepository)
    {
        $this->entityManager            = $categoryRepository->getEntityManager();
        $this->categoryRepository       = $categoryRepository;
        $this->categoryImageRepository  = $categoryImageRepository;
        $this->productRepository        = $productRepository;
        $this->orderRepository          = $orderRepository;
        $this->productVariantRepository = $productVariantRepository;
        $this->productImageRepository   = $imageRepository;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @return CategoryRepository
     */
    public function getCategoryRepository(): CategoryRepository
    {
        return $this->categoryRepository;
    }

    /**
     * @return CategoryImageRepository
     */
    public function getCategoryImageRepository(): CategoryImageRepository
    {
        return $this->categoryImageRepository;
    }


    /**
     * @return ProductRepository
     */
    public function getProductRepository(): ProductRepository
    {
        return $this->productRepository;
    }

    /**
     * @return OrderRepository
     */
    public function getOrderRepository(): OrderRepository
    {
        return $this->orderRepository;
    }

    /**
     * @return ProductVariantRepository
     */
    public function getProductVariantRepository(): ProductVariantRepository
    {
        return $this->productVariantRepository;
    }

    /**
     * @return ProductImageRepository
     */
    public function getProductImageRepository(): ProductImageRepository
    {
        return $this->productImageRepository;
    }

    public function createProductFeedLog(ProductEntity $productEntity, string $text)
    {
        $feedLog = new FeedLog($productEntity, $text);
        $this->productRepository->getEntityManager()->persist($feedLog);
    }

}