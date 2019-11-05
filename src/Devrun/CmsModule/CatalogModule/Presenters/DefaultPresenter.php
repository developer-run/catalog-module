<?php
/**
 * This file is part of the devrun2016
 * Copyright (c) 2017
 *
 * @file    DefaultPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CmsModule\CatalogModule\Presenters;

use Devrun\Application\UI\Presenter\TImgStoragePipe;
use Devrun\CatalogModule\Entities\CategoryEntity;
use Devrun\CatalogModule\Entities\CategoryImageEntity;
use Devrun\CatalogModule\Entities\ProductEntity;
use Devrun\CatalogModule\Repositories\CategoryRepository;
use Devrun\CmsModule\CatalogModule\Facades\CatalogFacade;
use Devrun\CmsModule\Controls\FlashMessageControl;
use Devrun\CmsModule\Presenters\AdminPresenter;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Gedmo\Translatable\Document\Repository\TranslationRepository;
use Kdyby\Doctrine\QueryBuilder;
use Nette\Forms\Container;
use Nette\Forms\Form;
use Nette\Http\Request;
use Nette\Http\Url;
use Nette\Utils\Html;


class DefaultPresenter extends AdminPresenter
{
    use TImgStoragePipe;


    /** @var CategoryRepository @inject */
    public $categoryRepository;

    /** @var CatalogFacade @inject */
    public $catalogFacade;

    /** @var Request @inject */
    public $url;



    public function renderDemo()
    {
        $em = $this->userFacade->getUserRepository()->getEntityManager();



//        $entity = new ProductEntity();
//        $entity->setTitle('my title in en');
//        $em->persist($entity);
//        $em->flush();


//        $entity = new ProductEntity();
//        $entity->setTitle('my title in de');
//        $entity->setName('Maine');
//        $entity->setTranslatableLocale('de_de');
//        $em->persist($entity);
//        $em->flush();



//        return;
        /** @var ProductEntity $entity */
        $entity = $em->find(ProductEntity::getClassName(), 1);

        $entity->setTranslatableLocale('ru_ru');
        $em->refresh($entity);


        $entity = $em->find(ProductEntity::getClassName(), 2);

        /** @var TranslationRepository $repo */
        $repo = $em->getRepository('Gedmo\Translatable\Entity\Translation');

        $translations = $repo->findTranslations($entity);

        dump($translations);
        dump($entity);

        return;
        $repo = $this->categoryRepository;

        dump($repo);
        die();


        $food = $repo->findOneByTitle('Food');
        dump($repo->childCount($food));

//        die();

// prints: 3
        dump($repo->childCount($food, true/*direct*/));

//        die();

// prints: 2
        $children = $repo->children($food);
        dump($children);
//        die();


// $children contains:
// 3 nodes
        $children = $repo->children($food, false, 'title');

        dump($children);
//        die();


// will sort the children by title
        $carrots = $repo->findOneByTitle('Carrots');
        dump($carrots);


        $path    = $repo->getPath($carrots);
        /* $path contains:
           0 => Food
           1 => Vegetables
           2 => Carrots
        */

        dump($path);

// verification and recovery of tree
        $repo->verify();
// can return TRUE if tree is valid, or array of errors found on tree
        $repo->recover();

//        $em->flush(); // important: flush recovered nodes

// if tree has errors it will try to fix all tree nodes

// UNSAFE: be sure to backup before running this method when necessary, if you can use $em->remove($node);
// which would cascade to children
// single node removal
        $vegies = $repo->findOneByTitle('Vegetables');
        dump($vegies);

        $repo->removeFromTree($vegies);
        $em->clear(); // clear cached nodes
// it will remove this node from tree and reparent all children

// reordering the tree
        $food = $repo->findOneByTitle('Food');
        dump($food);

        $repo->reorder($food, 'title');
// it will reorder all "Food" tree node left-right values by the title


        return;
        $food = new CategoryEntity();
        $food->setTitle('Food');

        $fruits = new CategoryEntity();
        $fruits->setTitle('Fruits');
        $fruits->setParent($food);

        $vegetables = new CategoryEntity();
        $vegetables->setTitle('Vegetables');
        $vegetables->setParent($food);

        $carrots = new CategoryEntity();
        $carrots->setTitle('Carrots');
        $carrots->setParent($vegetables);

        $em->persist($food);
        $em->persist($fruits);
        $em->persist($vegetables);
        $em->persist($carrots);
        $em->flush();


    }


    /**
     * @param $name
     *
     * @return \Devrun\CmsModule\Controls\DataGrid
     */
    protected function createComponentCategoryGridControl($name)
    {
        $grid       = $this->createGrid($name);
        $repository = $this->categoryRepository;
        $presenter  = $this;

        $query = $repository->createQueryBuilder('e')
            ->addSelect('p')
            ->leftJoin('e.products', 'p')
            ->andWhere('e.deletedBy IS NULL');


//        dump($query->getQuery()->getResult());
//        die();


        $grid->setDataSource($query);

        $grid->addColumnText('image', '')
            ->setFitContent()
            ->setRenderer(function (CategoryEntity $categoryEntity) use ($presenter) {

                $image = $categoryEntity->getImage()
                    ? $categoryEntity->getImage()
                    : new CategoryImageEntity($categoryEntity, 'categories/id/unknown.png');

                $img = $presenter->imgStorage->fromIdentifier([$image->getIdentifier(), '40x40']);
                $html = Html::el('img')->setAttribute('src', $this->url->url->basePath . $img->createLink());
                return $html;

            });

        $grid->addColumnLink('name', 'Kategorie', 'Category:edit')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('title', 'Titulek')
            ->setSortable()
            ->setFilterText();


        $grid->addColumnNumber('products', 'Produktů')
            ->setAlign('left')
            ->setRenderer(function (CategoryEntity $row) {
                return count($row->getProducts());
            });


        $grid->addColumnStatus('topped', 'Top')
            ->setFitContent()
            ->setCaret(true)
            ->addOption(false, 'Neaktivní')
            ->setClass('btn-default btn-block')
            ->endOption()
            ->addOption(true, 'Aktivní')
            ->setClass('btn-primary btn-block')
            ->endOption()
            ->setSortable()
            ->onChange[] = function ($id, $value) {
            /** @var CategoryEntity $entity */
            if ($entity= $this->categoryRepository->find($id)) {
                $entity->setTopped($value);
                $this->catalogFacade->getEntityManager()->persist($entity)->flush();
                $this->flashMessage("Kategorie {$entity->getName()} upravena", 'success');
                $this['categoryGridControl']->redrawItem($id);
                $this->ajaxRedirect('this', null, 'flash');
            }
        };

        $grid->getColumn('topped')->setFilterSelect([
            '' => $this->translator->translate('catalog.categoryVisibility.all'),
            false => $this->translator->translate('catalog.categoryVisibility.unpublished'),
            true => $this->translator->translate('catalog.categoryVisibility.published'),
        ])->addAttribute('class', 'btn-block btn');


        $grid->addColumnStatus('active', 'Aktivní')
            ->setFitContent()
            ->setCaret(true)
            ->addOption(false, 'Neaktivní')
            ->setClass('btn-default btn-block')
            ->endOption()
            ->addOption(true, 'Aktivní')
            ->setClass('btn-primary btn-block')
            ->endOption()
            ->setSortable()
            ->onChange[] = function ($id, $value) {
            /** @var CategoryEntity $entity */
            if ($entity= $this->categoryRepository->find($id)) {
                $entity->setActive($value);
                $this->catalogFacade->getEntityManager()->persist($entity)->flush();
                $this->flashMessage("Kategorie {$entity->getName()} upravena", 'success');
                $this['categoryGridControl']->redrawItem($id);
                $this->ajaxRedirect('this', null, 'flash');
            }
        };

        $grid->getColumn('active')->setFilterSelect([
            '' => $this->translator->translate('catalog.categoryVisibility.all'),
            false => $this->translator->translate('catalog.categoryVisibility.unpublished'),
            true => $this->translator->translate('catalog.categoryVisibility.published'),
        ])->addAttribute('class', 'btn-block btn');



        /*
         * add
         * _____________________________________________
         */
        $grid->addInlineAdd()
            ->setPositionTop()
            ->onControlAdd[] = function (Container $container)  {
            $container->addText('id', '')->setAttribute('readonly');//->setValue($this->editTemplateId);
            $container->addText('name');
            $container->addText('title');
            $container->addSelect('active', '', [false => 'catalog.categoryVisibility.unpublished', true => 'catalog.categoryVisibility.published']);
        };

        $grid->getInlineAdd()->onSubmit[] = function($values) use ($presenter) {

            try {
                $id = $values->id;

                /** @var CategoryEntity $entity */
                if (!$entity = $this->categoryRepository->find($id)) {
                    $entity = new CategoryEntity($this->translator, $values->name);
                }

                foreach ($values as $key => $value) {
                    if (isset($entity->$key)) {
                        $entity->$key = $value;
                    }
                }

                $entity->mergeNewTranslations();
                $this->catalogFacade->getEntityManager()->persist($entity)->flush();

            } catch (UniqueConstraintViolationException $e) {
                $message = "kategorie `{$entity->getName()}` exist, [error code {$e->getErrorCode()}]";
                $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, "Catalog add error", FlashMessageControl::TOAST_DANGER);
                $this->ajaxRedirect('this', null, ['flash']);
                return;
            }

            $message = "Kategorie [{$entity->getName()}] přidána!";
            $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa katalogu', FlashMessageControl::TOAST_SUCCESS);

            $this['categoryGridControl']->reload();
            $this->ajaxRedirect('this', null, ['flash']);
        };

        /*
         * edit
         * __________________________________________________
         */
        $grid->addInlineEdit()->setText('Edit')
            ->onControlAdd[] = function(Container $container) {

            $container->addText('name', '')
                ->setAttribute('placeholder', 'Default | Contains')
                ->addRule(Form::FILLED)
                ->addRule(Form::MIN_LENGTH, null, 3);

            $container->addText('title', '')
                ->setAttribute('placeholder', 'Titulek');

        };

        $grid->getInlineEdit()->onSetDefaults[] = function(Container $container, CategoryEntity $item) {

            $container->setDefaults([
                'id' => $item->id,
                'name' => $item->getName(),
                'title' => $item->getTitle(),
            ]);
        };

        $grid->getInlineEdit()->onSubmit[] = function($id, $values) use ($presenter) {

            /** @var CategoryEntity $entity */
            if ($entity= $this->categoryRepository->find($id)) {
                $entity
                    ->setName($values->name)
                    ->setTitle($values->title);

                $entity->mergeNewTranslations();
                $this->catalogFacade->getEntityManager()->persist($entity)->flush();

                $message = "Kategorie [{$entity->getName()}] upravena!";
                $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa katalogu', FlashMessageControl::TOAST_INFO);
                $this->ajaxRedirect('this', null, ['flash']);
            }
        };




//        $grid->addActionHref('editCategory', 'Upravit kategorii', 'Category:edit')
//            ->setIcon('edit fa-2x')
//            ->getElementPrototype()->addAttributes(array(
//                'data-popup-dialog' => 'popup',
//                'data-popup-title'  => "Úprava kategorie",
//                'data-popup-type'   => "modal-sm",
//            ));
//
//        $grid->addActionHref('editProducts', 'produkty', 'Category:productList')
//            ->setIcon('eye fa-2x');

        $grid->addToolbarButton(':Cms:Catalog:Product:', 'Produkty')
            ->addAttributes(['class' => 'btn-primary']);


        return $grid;
    }

}