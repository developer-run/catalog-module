<?php


namespace Devrun\CatalogModule\Managers;

use Devrun\CatalogModule\Entities\ProductEntity;

abstract class AbstractFeedManager
{

    /**
     * @param ProductEntity $productEntity
     * @return void
     */
    abstract protected function setSerial(ProductEntity & $productEntity);



}