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
use Devrun\Module\Providers\IPresenterMappingProvider;
use Kdyby\Console\DI\ConsoleExtension;
use Kdyby\Doctrine\DI\IEntityProvider;
use Kdyby\Doctrine\DI\OrmExtension;
use Kdyby\Events\DI\EventsExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Extensions\InjectExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * Class CatalogExtension|CompilerExtension
 *
 * @package Devrun\CatalogModule\DI
 */
class CatalogExtension extends CompilerExtension implements IEntityProvider, IPresenterMappingProvider
{

    public function getConfigSchema(): Schema
    {
        return Expect::structure([
            'feedXmlUrl'          => Expect::string(),
            'feedExpireTime'      => Expect::string('2 weeks'),
            'htmlExpireTime'      => Expect::string('1 weeks'),
            'filteredHtmlInCache' => Expect::bool(true),
            'update'              => Expect::structure([
                'new'    => Expect::structure([
                    'limit'  => Expect::int(2),
                    'enable' => Expect::bool(true),
                ]),
                'update' => Expect::structure([
                    'limit'  => Expect::int(2),
                    'enable' => Expect::bool(true),
                ]),
                'remove' => Expect::structure([
                    'limit'  => Expect::int(2),
                    'enable' => Expect::bool(true),
                ]),
            ]),

            'email' => Expect::structure([
                'send'    => Expect::bool(true),
                'from'    => Expect::string('Franta <example@email.com>'),
                'to'      => Expect::string('email@email.com'),
                'subject' => Expect::string('aktualizace'),
            ]),

        ]);
    }


    public function loadConfiguration()
    {
        parent::loadConfiguration();

        /** @var ContainerBuilder $builder */
        $builder = $this->getContainerBuilder();
        $config  = $this->getConfig();


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
                ->addTag(InjectExtension::TAG_INJECT);

        $builder->addDefinition($this->prefix('facade.catalogImage'))
                ->setType(Devrun\CmsModule\CatalogModule\Facades\CatalogImageFacade::class);

        $builder->addDefinition($this->prefix('facade.tracking'))
                ->setType(Devrun\CmsModule\CatalogModule\Facades\TrackingFacade::class);

        $builder->addDefinition($this->prefix('facade.feed'))
                ->setFactory(Devrun\CmsModule\CatalogModule\Facades\FeedFacade::class, ['options' => $config->update]);


        /*
         * managers
         */
        $builder->addDefinition($this->prefix('manager.feed'))
                ->setType(Devrun\CatalogModule\Managers\FeedManageManager::class)
                ->addSetup('setFeedXmlUrl', [$config->feedXmlUrl])
                ->addSetup('setFeedExpireTime', [$config->feedExpireTime])
                ->addSetup('setHtmlExpireTime', [$config->htmlExpireTime])
                ->addSetup('setFilteredHtmlInCache', [$config->filteredHtmlInCache])
                ->addTag(InjectExtension::TAG_INJECT, true);


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
        $builder->addFactoryDefinition($this->prefix('form.category'))
                ->setImplement('Devrun\CmsModule\CatalogModule\Forms\ICategoryFormFactory')
                ->addSetup('create')
                ->addSetup('bootstrap3Render')
                ->addTag(InjectExtension::TAG_INJECT);

        $builder->addFactoryDefinition($this->prefix('form.product'))
                ->setImplement('Devrun\CmsModule\CatalogModule\Forms\IProductFormFactory')
                ->addSetup('create')
                ->addSetup('bootstrap3Render')
                ->addTag(InjectExtension::TAG_INJECT);

        $builder->addFactoryDefinition($this->prefix('form.images'))
                ->setImplement('Devrun\CmsModule\CatalogModule\Forms\IImagesFormFactory')
                ->addSetup('create')
                ->addSetup('bootstrap3Render')
                ->addTag(InjectExtension::TAG_INJECT);

        $builder->addFactoryDefinition($this->prefix('form.image'))
                ->setImplement('Devrun\CmsModule\CatalogModule\Forms\IImageFormFactory')
                ->addSetup('create')
                ->addSetup('bootstrap3Render')
                ->addTag(InjectExtension::TAG_INJECT);

        $builder->addDefinition('cms.catalog.presenters.feed')
                ->setType(Devrun\CmsModule\CatalogModule\Presenters\FeedPresenter::class)
                ->addSetup('setFeedXmlUrl', [$config->feedXmlUrl])
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
        $builder->addFactoryDefinition($this->prefix('control.catalog'))
                ->setImplement('Devrun\CatalogModule\Controls\ICatalogControlFactory')
                ->addTag(InjectExtension::TAG_INJECT);

        $builder->addFactoryDefinition($this->prefix('control.products'))
                ->setImplement('Devrun\CatalogModule\Controls\IProductsControlFactory')
                ->addTag(InjectExtension::TAG_INJECT);

        $builder->addFactoryDefinition($this->prefix('control.categoryTree'))
                ->setImplement('Devrun\CatalogModule\Controls\ICategoryTreeControlFactory')
                ->addTag(InjectExtension::TAG_INJECT);

        $builder->addFactoryDefinition($this->prefix('control.search'))
                ->setImplement('Devrun\CatalogModule\Controls\ISearchControlFactory')
                ->addTag(InjectExtension::TAG_INJECT);

        $builder->addFactoryDefinition($this->prefix('control.carousel'))
                ->setImplement('Devrun\CatalogModule\Controls\ICarouselProductsControlFactory')
                ->addTag(InjectExtension::TAG_INJECT);


        /*
         * listeners
         */
        $builder->addDefinition($this->prefix('listeners.feed'))
                ->setFactory(Devrun\CatalogModule\Listeners\FeedListener::class, [$config->email])
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
     * @return array
     * @example return array('*' => 'Booking\*Module\Presenters\*Presenter');
     */
    public function getPresenterMapping()
    {
        return array(
            'Catalog' => "Devrun\\CatalogModule\\*Module\\Presenters\\*Presenter",
        );
    }

}