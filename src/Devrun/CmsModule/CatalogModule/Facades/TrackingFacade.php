<?php
/**
 * This file is part of affiliate-bagin.cz.
 * Copyright (c) 2019
 *
 * @file    TrackingFacade.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CmsModule\CatalogModule\Facades;


use Devrun\CatalogModule\Entities\CategoryEntity;
use Devrun\CatalogModule\Entities\ProductEntity;
use Devrun\CatalogModule\Entities\TrackingEntity;
use Devrun\CmsModule\Entities\RouteEntity;
use Kdyby\Doctrine\EntityManager;

class TrackingFacade
{

    /** @var EntityManager */
    private $entityManager;


    private $sessionId;


    /**
     * TrackingFacade constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @return mixed
     */
    protected function getSessionId()
    {
        if (!$this->sessionId) $this->sessionId = session_id();
        return $this->sessionId;
    }

    /**
     * @param string      $message
     * @param RouteEntity $routeEntity
     */
    public function createPageViewLog(string $message, RouteEntity $routeEntity)
    {
        $extra = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $entity = (new TrackingEntity($this->getSessionId(), TrackingEntity::ACTION_PAGE_VIEW, $message))
            ->setRoute($routeEntity)
            ->setExtra($extra)
            ->setIp(\Devrun\Utils\Debugger::getIPAddress());

        $this->entityManager->persist($entity)->flush();
    }

    /**
     * @param string        $message
     * @param ProductEntity $productEntity
     * @param RouteEntity   $routeEntity
     */
    public function createProductDetailViewLog(string $message, ProductEntity $productEntity, RouteEntity $routeEntity)
    {
        $extra = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $entity = (new TrackingEntity($this->getSessionId(), TrackingEntity::ACTION_PRODUCT_DETAIL, $message))
            ->setRoute($routeEntity)
            ->setProduct($productEntity)
            ->setExtra($extra)
            ->setIp(\Devrun\Utils\Debugger::getIPAddress());

        $this->entityManager->persist($entity)->flush();
    }

    /**
     * @param string         $message
     * @param CategoryEntity $categoryEntity
     * @param RouteEntity    $routeEntity
     */
    public function createCategoryViewLog(string $message, CategoryEntity $categoryEntity, RouteEntity $routeEntity)
    {
        $extra = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $entity = (new TrackingEntity($this->getSessionId(), TrackingEntity::ACTION_CATEGORY_LIST, $message))
            ->setRoute($routeEntity)
            ->setCategory($categoryEntity)
            ->setExtra($extra)
            ->setIp(\Devrun\Utils\Debugger::getIPAddress());

        $this->entityManager->persist($entity)->flush();
    }

    /**
     * @param string        $message
     * @param ProductEntity $productEntity
     * @param RouteEntity   $routeEntity
     */
    public function createProductBookLog(string $message, ProductEntity $productEntity, RouteEntity $routeEntity)
    {
        $extra = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $entity = (new TrackingEntity($this->getSessionId(), TrackingEntity::ACTION_BOOK, $message))
            ->setRoute($routeEntity)
            ->setProduct($productEntity)
            ->setExtra($extra)
            ->setIp(\Devrun\Utils\Debugger::getIPAddress());

        $this->entityManager->persist($entity)->flush();
    }


    /**
     * @param string $message
     */
    public function createSearchLog(string $message = null)
    {
        $extra = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $entity = (new TrackingEntity($this->getSessionId(), TrackingEntity::ACTION_SEARCH, $message))
            ->setExtra($extra)
            ->setIp(\Devrun\Utils\Debugger::getIPAddress());

        $this->entityManager->persist($entity)->flush();
    }


}