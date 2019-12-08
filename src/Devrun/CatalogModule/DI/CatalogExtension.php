<?php
/**
 * This file is part of the devrun2016
 * Copyright (c) 2017
 *
 * @file    CatalogExtension.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\DI;

use Devrun;
use Devrun\CatalogModule\Commands\UpdateCommand;
use Devrun\CatalogModule\Entities\CategoryEntity;
use Devrun\CatalogModule\Entities\CategoryImageEntity;
use Devrun\CatalogModule\Entities\OrderEntity;
use Devrun\CatalogModule\Entities\ProductEntity;
use Devrun\CatalogModule\Entities\ProductImageEntity;
use Devrun\CatalogModule\Entities\ProductVariantEntity;
use Devrun\CatalogModule\Repositories\ProductVariantRepository;
use Devrun\Config\CompilerExtension;
use Flame\Modules\Providers\IPresenterMappingProvider;
use Kdyby\Console\DI\ConsoleExtension;
use Kdyby\Doctrine\DI\IEntityProvider;
use Kdyby\Doctrine\DI\OrmExtension;
use Kdyby\Events\DI\EventsExtension;
use Nette\DI\ContainerBuilder;

/**
 * Class CatalogExtension|CompilerExtension
 *
 * @package Devrun\CatalogModule\DI
 */
class CatalogExtension extends CompilerExtension implements IEntityProvider, IPresenterMappingProvider
{
    public $defaults = array(
        'feedXmlUrl'          => '',
        'feedExpireTime'      => '2 weeks',
        'htmlExpireTime'      => '1 weeks',
        'filteredHtmlInCache' => true,
        'update' => [
            'new' => [
                'limit' => 2,
                'enable' => true,
            ],
            'update' => [
                'limit' => 2,
                'enable' => true,
            ],
            'remove' => [
                'limit' => 2,
                'enable' => true,
            ],
            'emailSend' => !"%debugMode%",
        ],

    );


    public function loadConfiguration()
    {
        parent::loadConfiguration();

        /** @var ContainerBuilder $builder */
        $builder = $this->getContainerBuilder();
        $config  = $this->getConfig($this->defaults);



        /*
         * commands
         */
        $builder->addDefinition($this->prefix('updateCommand'))
                ->setFactory(UpdateCommand::class)
                ->addTag(ConsoleExtension::TAG_COMMAND);


        /*
         * facade services
         */
        $builder->addDefinition($this->prefix('facade.catalog'))
            ->setType(Devrun\CmsModule\CatalogModule\Facades\CatalogFacade::class)
            ->setInject();

        $builder->addDefinition($this->prefix('facade.catalogImage'))
            ->setType(Devrun\CmsModule\CatalogModule\Facades\CatalogImageFacade::class);

        $builder->addDefinition($this->prefix('facade.tracking'))
            ->setType(Devrun\CmsModule\CatalogModule\Facades\TrackingFacade::class);

        $builder->addDefinition($this->prefix('facade.feed'))
            ->setFactory(Devrun\CmsModule\CatalogModule\Facades\FeedFacade::class, ['options' => $config['update']]);


        /*
         * managers
         */
        $builder->addDefinition($this->prefix('manager.feed'))
                ->setType(Devrun\CatalogModule\Managers\FeedManageManager::class)
                ->addSetup('setFeedXmlUrl', [$config['feedXmlUrl']])
                ->addSetup('setFeedExpireTime', [$config['feedExpireTime']])
                ->addSetup('setHtmlExpireTime', [$config['htmlExpireTime']])
                ->addSetup('setFilteredHtmlInCache', [$config['filteredHtmlInCache']])
                ->setInject(true);



        /*
         * repositories factory
         */
        $builder->addDefinition($this->prefix('repository.order'))
            ->setType('Devrun\CatalogModule\Repositories\OrderRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, OrderEntity::class);

        $builder->addDefinition($this->prefix('repository.catalog'))
            ->setType('Devrun\CatalogModule\Repositories\CategoryRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, CategoryEntity::class);

        $builder->addDefinition($this->prefix('repository.catalogCategoryImage'))
            ->setType('Devrun\CatalogModule\Repositories\CategoryImageRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, CategoryImageEntity::class);

        $builder->addDefinition($this->prefix('repository.product'))
            ->setType('Devrun\CatalogModule\Repositories\ProductRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, ProductEntity::class);

        $builder->addDefinition($this->prefix('repository.productVariant'))
            ->setType(ProductVariantRepository::class)
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, ProductVariantEntity::class);

        $builder->addDefinition($this->prefix('repository.productImage'))
            ->setType('Devrun\CatalogModule\Repositories\ProductImageRepository')
            ->addTag(OrmExtension::TAG_REPOSITORY_ENTITY, ProductImageEntity::class);

        $builder->addDefinition($this->prefix('repository.sitemap'))
                ->setType('Devrun\CatalogModule\Repositories\SitemapRepository');


        /*
         * forms factory
         */
        $builder->addDefinition($this->prefix('form.category'))
            ->setImplement('Devrun\CmsModule\CatalogModule\Forms\ICategoryFormFactory')
            ->addSetup('create')
            ->addSetup('bootstrap3Render')
            ->setInject();

        $builder->addDefinition($this->prefix('form.product'))
            ->setImplement('Devrun\CmsModule\CatalogModule\Forms\IProductFormFactory')
            ->addSetup('create')
            ->addSetup('bootstrap3Render')
            ->setInject();

        $builder->addDefinition($this->prefix('form.images'))
            ->setImplement('Devrun\CmsModule\CatalogModule\Forms\IImagesFormFactory')
            ->addSetup('create')
            ->addSetup('bootstrap3Render')
            ->setInject();

        $builder->addDefinition($this->prefix('form.image'))
            ->setImplement('Devrun\CmsModule\CatalogModule\Forms\IImageFormFactory')
            ->addSetup('create')
            ->addSetup('bootstrap3Render')
            ->setInject();


        $builder->addDefinition('cms.catalog.presenters.feed')
            ->setType(Devrun\CmsModule\CatalogModule\Presenters\FeedPresenter::class)
            ->addSetup('setFeedXmlUrl', [$config['feedXmlUrl']])
            ->addTag(Devrun\Utils\PresenterUtil::DEVRUN_PRESENTER_TAG)
                ->addTag('administration', [
                    'category'    => 'modules.catalog',
                    'name'        => 'messages.feed.name',
                    'description' => 'messages.feed.title',
                    'link'        => ':Cms:Catalog:Feed:default',
                    'icon'        => 'fa-credit-card',
                    'priority'    => 40,
                ]);




        /*
         * controls factory
         */
        $builder->addDefinition($this->prefix('control.catalog'))
            ->setImplement('Devrun\CatalogModule\Controls\ICatalogControlFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('control.products'))
            ->setImplement('Devrun\CatalogModule\Controls\IProductsControlFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('control.categoryTree'))
            ->setImplement('Devrun\CatalogModule\Controls\ICategoryTreeControlFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('control.search'))
            ->setImplement('Devrun\CatalogModule\Controls\ISearchControlFactory')
            ->setInject(true);

        $builder->addDefinition($this->prefix('control.carousel'))
            ->setImplement('Devrun\CatalogModule\Controls\ICarouselProductsControlFactory')
            ->setInject(true);


        /*
         * listeners
         */
        $builder->addDefinition($this->prefix('listeners.feed'))
                ->setFactory(Devrun\CatalogModule\Listeners\FeedListener::class, [$config['update']['emailSend']])
                ->addTag(EventsExtension::TAG_SUBSCRIBER);


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