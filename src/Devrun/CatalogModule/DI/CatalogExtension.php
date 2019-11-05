<?php
/**
 * This file is part of the devrun2016
 * Copyright (c) 2017
 *
 * @file    CatalogExtension.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\DI;

use Devrun\CatalogModule\Entities\CategoryEntity;
use Devrun\CatalogModule\Entities\CategoryImageEntity;
use Devrun\CatalogModule\Entities\OrderEntity;
use Devrun\CatalogModule\Entities\ProductEntity;
use Devrun\CatalogModule\Entities\ProductImageEntity;
use Devrun\CatalogModule\Entities\ProductVariantEntity;
use Devrun\CatalogModule\Repositories\ProductVariantRepository;
use Devrun\Config\CompilerExtension;
use Flame\Modules\Providers\IPresenterMappingProvider;
use Flame\Modules\Providers\IRouterProvider;
use Kdyby\Doctrine\DI\IEntityProvider;
use Kdyby\Doctrine\DI\OrmExtension;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\DI\ContainerBuilder;

/**
 * Class CatalogExtension|CompilerExtension
 *
 * @package Devrun\CatalogModule\DI
 */
class CatalogExtension extends CompilerExtension implements IEntityProvider, IPresenterMappingProvider
{
    public $defaults = array(
        'autoImagesGarbage' => true, // auto check images after uploads
    );


    public function loadConfiguration()
    {
        parent::loadConfiguration();

        /** @var ContainerBuilder $builder */
        $builder = $this->getContainerBuilder();



        /*
         * facade services
         */
        $builder->addDefinition($this->prefix('facade.catalogFacade'))
            ->setFactory('Devrun\CmsModule\CatalogModule\Facades\CatalogFacade')
            ->setInject();

        $builder->addDefinition($this->prefix('facade.catalogImageFacade'))
            ->setFactory('Devrun\CmsModule\CatalogModule\Facades\CatalogImageFacade');

        $builder->addDefinition($this->prefix('facade.trackingFacade'))
            ->setFactory('Devrun\CmsModule\CatalogModule\Facades\TrackingFacade');



        /*
         * repositories factory
         */
        $builder->addDefinition($this->prefix('repository.orderRepository'))
            ->setFactory('Devrun\CatalogModule\Repositories\OrderRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, OrderEntity::class);

        $builder->addDefinition($this->prefix('repository.catalogRepository'))
            ->setFactory('Devrun\CatalogModule\Repositories\CategoryRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, CategoryEntity::class);

        $builder->addDefinition($this->prefix('repository.catalogCategoryImageRepository'))
            ->setFactory('Devrun\CatalogModule\Repositories\CategoryImageRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, CategoryImageEntity::class);

        $builder->addDefinition($this->prefix('repository.productRepository'))
            ->setFactory('Devrun\CatalogModule\Repositories\ProductRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, ProductEntity::class);

        $builder->addDefinition($this->prefix('repository.productVariantRepository'))
            ->setFactory(ProductVariantRepository::class)
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, ProductVariantEntity::class);

        $builder->addDefinition($this->prefix('repository.productImageRepository'))
            ->setFactory('Devrun\CatalogModule\Repositories\ProductImageRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, ProductImageEntity::class);


        /*
         * forms factory
         */
        $builder->addDefinition($this->prefix('form.categoryForm'))
            ->setImplement('Devrun\CmsModule\CatalogModule\Forms\ICategoryFormFactory')
            ->addSetup('create')
            ->addSetup('bootstrap3Render')
            ->setInject();

        $builder->addDefinition($this->prefix('form.productForm'))
            ->setImplement('Devrun\CmsModule\CatalogModule\Forms\IProductFormFactory')
            ->addSetup('create')
            ->addSetup('bootstrap3Render')
            ->setInject();

        $builder->addDefinition($this->prefix('form.imagesForm'))
            ->setImplement('Devrun\CmsModule\CatalogModule\Forms\IImagesFormFactory')
            ->addSetup('create')
            ->addSetup('bootstrap3Render')
            ->setInject();

        $builder->addDefinition($this->prefix('form.imageForm'))
            ->setImplement('Devrun\CmsModule\CatalogModule\Forms\IImageFormFactory')
            ->addSetup('create')
            ->addSetup('bootstrap3Render')
            ->setInject();


        /*
         * controls factory
         */
        $builder->addDefinition($this->prefix('control.catalogControlFactory'))
            ->setImplement('Devrun\CatalogModule\Controls\ICatalogControlFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('control.productsControlFactory'))
            ->setImplement('Devrun\CatalogModule\Controls\IProductsControlFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('control.categoryTreeControlFactory'))
            ->setImplement('Devrun\CatalogModule\Controls\ICategoryTreeControlFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('control.searchControlFactory'))
            ->setImplement('Devrun\CatalogModule\Controls\ISearchControlFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('control.carouselProductsControlFactory'))
            ->setImplement('Devrun\CatalogModule\Controls\ICarouselProductsControlFactory')
            ->setInject(true);


        /*
         * listeners
         */


    }





    /**
     * Returns associative array of Namespace => mapping definition
     *
     * @return array
     */
    function getEntityMappings()
    {
        return array(
            'Devrun\CatalogModule' => dirname(__DIR__) . '/Entities/',
        );
    }

    /**
     * Returns array of ClassNameMask => PresenterNameMask
     *
     * @example return array('*' => 'Booking\*Module\Presenters\*Presenter');
     * @return array
     */
    public function getPresenterMapping()
    {
        return array(
            'Catalog' => "Devrun\\CatalogModule\\*Module\\Presenters\\*Presenter",
        );
    }

}