<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    ImageFacade.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CmsModule\CatalogModule\Facades;

use Devrun\CatalogModule\Entities\ProductEntity;
use Devrun\CatalogModule\Entities\ProductImageEntity;
use Devrun\CatalogModule\Repositories\ImagesRepository;
use Devrun\CatalogModule\Repositories\ProductImageRepository;
use Devrun\CatalogModule\Repositories\ProductRepository;
use Devrun\Facades\ImageFacade;
use Devrun\Storage\ImageStorage;
use Nette\Utils\Finder;

class CatalogImageFacade
{

    const CATALOG_IMAGE_NAMESPACE = 'product';


    /** @var ImageStorage */
    private $imageService;

    /** @var ProductImageRepository */
    private $productImageRepository;

    /**
     * CatalogImageFacade constructor.
     *
     * @param ImageStorage           $imageService
     * @param ProductImageRepository $productImageRepository
     */
    public function __construct(ImageStorage $imageService, ProductImageRepository $productImageRepository)
    {
        $this->imageService           = $imageService;
        $this->productImageRepository = $productImageRepository;
    }


    /**
     * @return ProductImageRepository
     */
    public function getProductImageRepository()
    {
        return $this->productImageRepository;
    }


    public function setProductMainImage(ProductEntity $productEntity, ProductImageEntity $imageEntity)
    {
        $this->productImageRepository->getEntityManager()->getConnection()->beginTransaction();

        $this->productImageRepository->createQueryBuilder()
            ->update(ProductImageEntity::class, 'e')
            ->leftJoin('e.product', 'p')
            ->set('e.main', '?1')
            ->where('e.product = :product')
            ->setParameter(1, false)
            ->setParameter('product', $productEntity)
            ->getQuery()
            ->execute();

        $imageEntity->setMain(true);

        $this->productImageRepository->getEntityManager()->persist($imageEntity)->flush();
        $this->productImageRepository->getEntityManager()->getConnection()->commit();

    }


    public function removeImage($id)
    {
        /** @var ProductImageEntity $imageEntity */
        if ($imageEntity = $this->productImageRepository->find($id)) {

            try {
                $this->imageService->delete($imageEntity->getIdentifier());
                $this->productImageRepository->getEntityManager()->remove($imageEntity)->flush();
                return $imageEntity;

            } catch (\Nette\InvalidStateException $e) {

            }
        }

        return false;
    }


    private function getNamespace(ProductEntity $productEntity)
    {
        return self::CATALOG_IMAGE_NAMESPACE . DIRECTORY_SEPARATOR . $productEntity->getId();
    }


    private function getDir(ProductEntity $productEntity)
    {
        return DIRECTORY_SEPARATOR . $this->getNamespace($productEntity) . DIRECTORY_SEPARATOR . ImageFacade::IMAGE_PREFIX;
    }

}