<?php


namespace Devrun\CmsModule\CatalogModule\Facades;

use Devrun\CatalogModule\Entities\ProductEntity;
use Devrun\CatalogModule\Managers\IFeedManage;
use Devrun\CatalogModule\Repositories\CategoryRepository;
use Devrun\CatalogModule\Repositories\ProductRepository;
use Devrun\CmsModule\CatalogModule\Managers\BagInFeed;
use Devrun\CmsModule\Entities\PackageEntity;
use Devrun\CmsModule\Repositories\PageRepository;
use Devrun\Doctrine\Repositories\UserRepository;
use Devrun\Security\User;
use Kdyby\Doctrine\EntityManager;
use Nette\SmartObject;

/**
 * Class FeedFacade
 * @package Devrun\CmsModule\CatalogModule\Facades
 * @method onUpdate(FeedFacade $class, array $toNew, array $toUpdate, array $toRemove)
 */
class FeedFacade
{
    const FEED_EVENT = "Devrun\CmsModule\CatalogModule\Facades\FeedFacade::onUpdate";

    use SmartObject;

    /** @var CategoryRepository */
    private $categoryRepository;

    /** @var ProductRepository */
    private $productRepository;

    /** @var PageRepository */
    private $pageRepository;

    /** @var IFeedManage */
    private $feedManager;

    /** @var UserRepository */
    private $userRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var User */
    private $user;

    /** @var array */
    public $onUpdate = [];

    /** @var array */
    private $options = [];


    /**
     * FeedFacade constructor.
     * @param array $options
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository $productRepository
     * @param IFeedManage $feedManager
     * @param UserRepository $userRepository
     * @param User $user
     */
    public function __construct(array $options, CategoryRepository $categoryRepository, ProductRepository $productRepository, IFeedManage $feedManager, UserRepository $userRepository, User $user, PageRepository $pageRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository  = $productRepository;
        $this->feedManager        = $feedManager;
        $this->options            = $options;
        $this->user               = $user;
        $this->userRepository     = $userRepository;
        $this->pageRepository     = $pageRepository;
        $this->entityManager      = $productRepository->getEntityManager();
    }

    /**
     * @return IFeedManage
     */
    public function getFeedManager(): IFeedManage
    {
        return $this->feedManager;
    }


    /**
     * update / synchronize
     */
    public function update()
    {
        try {
            $exists = $this->productRepository->findAssoc([], 'id');

        } catch (\Exception $e) {
            $exists = [];
            throw new $e;
        }

        $feedProducts = $this->feedManager->getDataSource();

        $removed = $updated = $news = [];

        if ($this->options['remove']['enable']) {

            /** @var ProductEntity[] $toRemove */
            $toRemove = array_diff_key($exists, $feedProducts);
            $toRemove = array_filter($toRemove, function (ProductEntity $productEntity) {
                return !$productEntity->getDeletedBy();
            });

            $removed = $this->removeAction($toRemove);
        }



        if ($this->options['new']['enable']) {
            $toNew = array_diff_key($feedProducts, $exists);
            $news = $this->newAction($toNew);
        }



        if ($this->options['update']['enable']) {
            $toUpdate = array_intersect_key($feedProducts, $exists);
            $toUpdate = array_filter($toUpdate, function (BagInFeed $feed) use ($exists) {

                /** @var ProductEntity $product */
                $product = $exists[$feed->id];

                return $this->feedManager->getSerial($feed) != $product->getSerial();
            });

            $updated = $this->updateAction($toUpdate);
        }

        $this->onUpdate($this, $news, $updated, $removed);
        if ($news || $updated || $removed) {
            $this->entityManager->flush();
        }

    }


    /**
     * @param ProductEntity[] $items
     * @return ProductEntity[]
     */
    protected function removeAction(array $items)
    {
        $removed = [];

        if ($userEntity = $this->getUserEntity()) {
            $maxLimit = $this->options['remove']['limit'];

            foreach ($items as $item) {
                if ($maxLimit-- == 0) break;

                $item->setDeletedBy($userEntity);
                $this->entityManager->persist($item);
                $removed[] = $item;
            }
        }

        return $removed;
    }


    /**
     * @param BagInFeed[] $items
     * @return BagInFeed[]
     */
    protected function newAction(array $items)
    {
        $news = [];

        if ($userEntity = $this->getUserEntity()) {
            $maxLimit = $this->options['new']['limit'];

            $this->feedManager
                ->setPageCatalogListEntity($this->pageRepository->findOneBy(['name' => 'front:cataloglist:default']))
                ->setPageProductDetailEntity($this->pageRepository->findOneBy(['name' => 'front:product:default']))
                ->setPackageEntity($this->getUsePackageEntity());

            foreach ($items as $item) {
                if ($maxLimit-- == 0) break;
//                $this->feedManager->synchronize($item->id);
                $news[] = $item;
            }
        }

        return $news;
    }


    /**
     * @param BagInFeed[] $items
     * @return BagInFeed[]
     */
    protected function updateAction(array $items)
    {
        $updated = [];

        if ($userEntity = $this->getUserEntity()) {
            $maxLimit = $this->options['update']['limit'];

            $this->feedManager
                ->setPageCatalogListEntity($this->pageRepository->findOneBy(['name' => 'front:cataloglist:default']))
                ->setPageProductDetailEntity($this->pageRepository->findOneBy(['name' => 'front:product:default']))
                ->setPackageEntity($this->getUsePackageEntity());

            foreach ($items as $item) {
                if ($maxLimit-- == 0) break;

//                $this->feedManager->synchronize($item->id);
                $updated[] = $item;
            }
        }

        return $updated;
    }



    private function getUserEntity()
    {
        static $userEntity = null;

        if( $this->user->isLoggedIn()) {
            $userEntity = $this->userRepository->find($this->user->getIdentity()->getId());
        }

        if (!$userEntity) {
            $userEntity = $this->userRepository->findOneBy(['username' => 'robot']);
        }

        if (!$userEntity) {
            // no any user find
        }

        return $userEntity;
    }


    /**
     * @return PackageEntity
     */
    public function getUsePackageEntity(): PackageEntity
    {
        static $usePackageEntity = null;

        if (!$usePackageEntity) {
            $usePackageEntity = $this->productRepository->getEntityManager()
                                                        ->getRepository(PackageEntity::class)
                                                        ->findOneBy(['module' => 'front', 'name' => 'Default']);
        }

        return $usePackageEntity;
    }


}