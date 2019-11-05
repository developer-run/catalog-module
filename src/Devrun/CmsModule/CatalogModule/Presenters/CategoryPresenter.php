<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    CategoryPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CmsModule\CatalogModule\Presenters;

use Devrun\Application\UI\Presenter\TImgStoragePipe;
use Devrun\CatalogModule\Entities\CategoryEntity;
use Devrun\CatalogModule\Entities\CategoryImageEntity;
use Devrun\CatalogModule\Entities\ProductEntity;
use Devrun\CatalogModule\Entities\ProductImageEntity;
use Devrun\CatalogModule\Repositories\ProductRepository;
use Devrun\CmsModule\CatalogModule\Facades\CatalogFacade;
use Devrun\CmsModule\Controls\FlashMessageControl;
use Devrun\CmsModule\Facades\ImageManageFacade;
use Devrun\CmsModule\Forms\DevrunForm;
use Devrun\CmsModule\Presenters\AdminPresenter;
use Devrun\Utils\Pattern;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Kdyby\Doctrine\QueryBuilder;
use Nette\Forms\Container;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Nette\Http\Request;
use Nette\Utils\DateTime;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

class CategoryPresenter extends AdminPresenter
{

    use TImgStoragePipe;


    /** @var CatalogFacade @inject */
    public $catalogFacade;

    /** @var ImageManageFacade @inject */
    public $imageManageFacade;


    /** @var ProductRepository @inject */
    public $productRepository;

    /** @var Request @inject */
    public $requestUrl;

    /** @var CategoryEntity */
    private $categoryEntity;


    public function actionEdit($id)
    {
        if (!$this->categoryEntity = $this->catalogFacade->getCategoryRepository()->find($id)) {
            $this->flashMessage("Kategorie $id nenalezena ", 'danger');
            $this->ajaxRedirect(':Cms:Catalog:Default:');
        }

        $this->template->category = $this->categoryEntity;
        $this->template->image = $this->categoryEntity->getImage();
    }


    public function actionProductList($id)
    {
        if (!$this->categoryEntity = $this->catalogFacade->getCategoryRepository()->find($id)) {
            $this->flashMessage("Kategorie $id nenalezena ", 'danger');
            $this->ajaxRedirect(':Cms:Catalog:Default:');
        }

        $this->template->category = $this->categoryEntity;
    }

    public function actionAddProduct($id)
    {
        if (!$this->categoryEntity = $this->catalogFacade->getCategoryRepository()->find($id)) {
            $this->flashMessage("Kategorie $id nenalezena ", 'danger');
            $this->ajaxRedirect(':Cms:Catalog:Default:');
        }


    }


    public function handleDeleteProduct($id, $pID)
    {
        /** @var ProductEntity $productEntity */
        if (!$productEntity = $this->productRepository->find($pID)) {
            $this->flashMessage("Produkt $pID nenalezen ", 'danger');
            $this->ajaxRedirect(':Cms:Catalog:Default:');
        }

        $this->productRepository->getEntityManager()->remove($productEntity)->flush();

        $message = "produkt `{$productEntity->getName()}` smazán";
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, "Správa produktů", FlashMessageControl::TOAST_INFO);

        if ($this->isAjax()) {
            $this->redrawControl('flash');
        } else {
            $this->redirect(':Cms:Catalog:Category:edit', ['id' => $id]);
        }
    }


    public function setActives(array $ids)
    {

        /** @var ProductEntity[] $nonActives */
        $nonActives = $this->productRepository->findBy(['id != :ids' => $ids]);

        /** @var ProductEntity[] $actives */
        $actives    = $this->productRepository->findBy(['id = :ids' => $ids]);

        foreach ($nonActives as $nonActive) {
            $nonActive->setActive(false);
        }

        foreach ($actives as $active) {
            $active->setActive(true);
        }

        $this->productRepository->getEntityManager()->persist($nonActives)->persist($actives)->flush();

        if ($this->isAjax()) {
            $this['categoryProductsGridControl']->reload();
        } else {
            $this->redirect('this');
        }
    }










    public function actionRemoveCategory($id)
    {

    }




    /*
     * ------------------------------------------------------------------------------------
     * factories
     * ------------------------------------------------------------------------------------
     */


    protected function createComponentCategoryProductsGridControl($name)
    {

        $grid = $this->createGrid($name);
        $presenter = $this;
/*
        $qb = $this->catalogFacade->getProductRepository()
            ->createQueryBuilder('a')
            ->leftJoin('a.categories', 'c')
            ->where('c.id = :id')->setParameter('id', 4)
            ->getQuery()
            ->getResult();
*/

//        dump($qb);
//        die();




        $query = $this->productRepository->createQueryBuilder('e')
            ->addSelect('i')
            ->leftJoin('e.categories', 'c')
            ->leftJoin('e.images', 'i')
            ->andWhere('c.id = :category')->setParameter('category', $this->categoryEntity->getId())
            ;


        $grid->setDataSource($query);

        $grid->addColumnLink('photo', '')
            ->setFitContent()
            ->setRenderer(function (ProductEntity $productEntity) use ($presenter) {

                /** @var ProductImageEntity $image */
                if (!$image = $productEntity->getMainPhoto()) {
                    $image = new ProductImageEntity($productEntity, 'products/id/unknown.png');
                }

                $img = $presenter->imgStorage->fromIdentifier([$image->getIdentifier(), '70x70']);
                $html = Html::el('img')->setAttribute('src', $this->requestUrl->getUrl()->getBasePath() . $img->createLink());
                return $html;

            });

        $grid->addColumnLink('name', 'Název', 'Product:edit')
            ->addParameters(['categoryId' => $this->template->category->id])
            ->setSortable()
            ->setFilterText();



        $grid->addColumnText('title', 'Titulka')
            ->setSortable()
            ->setFilterText();


        $grid->addColumnText('price', 'Cena')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnNumber('amount', 'Množství na 1ks')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnNumber('inStock', 'Na skladě')
            ->setSortable()
            ->setFilterText();


        $grid->addColumnDateTime('publishedFrom', 'Od')
            ->setFitContent()
            ->setFormat('Y-m-d H:i')
            ->setSortable()
//            ->setAlign('left')
            ->setFilterDate()
            ->setCondition(function (QueryBuilder $qb, $value) {
                $date = DateTime::createFromFormat("d. m. Y", $value)->setTime(0,0,0);
                $qb->andWhere('e.publishedFrom >= :from')->setParameter('from', $date);
            });

        $grid->addColumnDateTime('publishedTo', 'Do')
            ->setFitContent()
            ->setAlign('left')
            ->setFormat('Y-m-d H:i')
            ->setSortable()
            ->setFilterDate()
            ->setCondition(function (QueryBuilder $qb, $value) {
                $date = DateTime::createFromFormat("d. m. Y", $value)->setTime(0,0,0);
                $qb->andWhere('e.publishedTo <= :to')->setParameter('to', $date);
            });



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
            /** @var ProductEntity $entity */
            if ($entity= $this->productRepository->find($id)) {
                $entity->setActive($value);
                $this->productRepository->getEntityManager()->persist($entity)->flush();
                $this->flashMessage("Produkt {$entity->getName()} upraven", 'success');
                $this['categoryProductsGridControl']->redrawItem($id);
                $this->ajaxRedirect('this', null, 'flash');
            }
        };

        $grid->getColumn('active')->setFilterSelect([
            '' => $this->translator->translate('product.visibility.all'),
            false => $this->translator->translate('product.visibility.active'),
            true => $this->translator->translate('product.visibility.disActive'),
        ])->addAttribute('class', 'btn-block btn');






        /*
         * add
         * _____________________________________________
         */
        $presenter = $this;
        $grid->addInlineAdd()
            ->setPositionTop()
            ->onControlAdd[] = function (Container $container)  {
            $container->addText('id', '')->setAttribute('readonly');//->setValue($this->editTemplateId);
            $container->addText('name');
            $container->addText('title');
        };

        $grid->getInlineAdd()->onSubmit[] = function($values) use ($presenter) {

            try {
                $id = $values->id;

                /** @var ProductEntity $entity */
                if (!$entity = $this->productRepository->find($id)) {
                    $entity = new ProductEntity($this->translator, $values->name);
                    $entity->addCategory($this->categoryEntity);
                }

                foreach ($values as $key => $value) {
                    if (isset($entity->$key) & $value) {
                        $entity->$key = $value;
                    }
                }

                $entity->mergeNewTranslations();

                $this->productRepository->getEntityManager()->persist($entity)->flush();

            } catch (UniqueConstraintViolationException $e) {
                $message = "product `{$entity->getName()}` exist, [error code {$e->getErrorCode()}]";
                $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, "Product add error", FlashMessageControl::TOAST_DANGER);
                $this->ajaxRedirect('this', null, ['flash']);
                return;
            }

            $message = "Kategorie [{$entity->getName()}] přidána!";
            $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa katalogu', FlashMessageControl::TOAST_SUCCESS);

            $this['categoryProductsGridControl']->reload();
            $this->ajaxRedirect('this', null, ['flash']);
        };

        /*
         * edit
         * __________________________________________________
         */
        $grid->addInlineEdit()->setText('Edit')
            ->onControlAdd[] = function(Container $container) {


            $container->addText('name')
                ->setAttribute('placeholder', 'Default | Contains')
                ->addRule(Form::FILLED)
                ->addRule(Form::MIN_LENGTH, null, 3);

            $container->addText('title')
                ->setAttribute('placeholder', 'Titulek');

            $container->addText('price')
                ->setAttribute('placeholder', 'Cena')
                ->addCondition(Form::FILLED)
                ->addRule(Form::FLOAT);

            $container->addText('publishedFrom')
                ->setAttribute('placeholder', 'Publikován od')
                ->setAttribute('data-provide', "datepicker")
                ->setAttribute('data-date-orientation', "bottom")
                ->setAttribute('data-date-format', "yyyy-mm-dd")
                ->addCondition(Form::FILLED)
                ->addRule(Form::PATTERN, 'Vyplňte prosím položku ve formátu YYYY-MM-DD', Pattern::DATE);

            $container->addText('publishedTo')
                ->setAttribute('placeholder', 'Publikován do')
                ->setAttribute('data-provide', "datepicker")
                ->setAttribute('data-date-orientation', "bottom")
                ->setAttribute('data-date-format', "yyyy-mm-dd")
                ->addCondition(Form::FILLED)
                ->addRule(Form::PATTERN, 'Vyplňte prosím položku ve formátu YYYY-MM-DD', Pattern::DATE);

            $container->addText('amount')
                ->setAttribute('placeholder', 'Množství')
                ->addRule(Form::FILLED)
                ->addRule(Form::NUMERIC)
                ->addRule(Form::RANGE, 'Množství musí být v rozsahu mezi %d a %d.', [1, PHP_INT_MAX]);

            $container->addText('inStock', '')
                ->setType('number')
                ->setAttribute('placeholder', 'Počet kusů na skladě');

        };

        $grid->getInlineEdit()->onSetDefaults[] = function(Container $container, ProductEntity $item) {

            $container->setDefaults([
                'id' => $item->id,
                'name' => $item->getName(),
                'title' => $item->getTitle(),
                'price' => $item->getPrice(),
                'publishedFrom' =>  $item->getPublishedFrom() ? $item->getPublishedFrom()->format('Y-m-d') : null,
                'publishedTo' => $item->getPublishedTo() ? $item->getPublishedTo()->format('Y-m-d') : null,
                'amount' => $item->getAmount(),
                'inStock' => $item->getInStock(),
            ]);
        };

        $grid->getInlineEdit()->onSubmit[] = function($id, $values) use ($presenter) {

            /** @var ProductEntity $entity */
            if ($entity= $this->productRepository->find($id)) {

                foreach ($values as $key => $value) {
                    if (isset($entity->$key)) {
                        $entity->$key = $value;
                    }
                }

                $entity->mergeNewTranslations();
                $this->productRepository->getEntityManager()->persist($entity)->flush();

                $message = "Product [{$entity->getName()}] upraven!";
                $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa produktu', FlashMessageControl::TOAST_INFO);
                $this->ajaxRedirect('this', null, ['flash']);
            }
        };


        $grid->addAction('deleteProduct', 'Smazat', 'deleteProduct!')
            ->addParameters(['cID' => $this->categoryEntity->id])
            ->setRenderer(function (ProductEntity $entity) {
                $href = Html::el('a')->setText('Smazat')->href($this->link('deleteProduct!', ['id' => $this->categoryEntity->getId(), 'pID' => $entity->getId()]));
                $href->setAttribute('class', 'btn btn-xs btn-danger');

                return $href;
            })
            ->setIcon('trash')
            ->setClass('_ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat produkt [id: {$item->id} {$item->name}]?";
            });


        $grid->addGroupAction('Aktivní')->onSelect[] = [$this, 'setActives'];


        return $grid;
    }



    /**
     * @param $name
     *
     * @return \Devrun\CmsModule\CatalogModule\Forms\CategoryForm
     */
    protected function createComponentCategoryForm($name)
    {
        $form = $this->catalogFacade->categoryFormFactory->create();
        $form->bindEntity($entity = $this->categoryEntity);

        $form->setDefaults([
            'title' => $entity->getTitle(),
            'description' => $entity->getDescription(),
        ])->onSuccess[] = function (DevrunForm $form, $values) {

            /** @var CategoryEntity $entity */
            $entity = $form->getEntity();

            foreach ($values as $key => $value) {
                if (isset($entity->$key) && $value) {
                    $entity->$key = $value;
                }
            }

            /** @var FileUpload $image */
            $image = $values->imageUpload;

            $imageJob = $this->imageManageFacade->getImageJob()
                ->setImageRepository($this->catalogFacade->getCategoryImageRepository());

            $imageEntity = null;

            if ($image->isOk() && $image->isImage()) {

                $imageJob->callCreateImageEntity = function ($referenceIdentifier) {

                    // @todo pozor na new image !!!
                    $categoryImageEntity = new CategoryImageEntity($this->categoryEntity, $referenceIdentifier);
                    $this->categoryEntity->setImage($categoryImageEntity);
                    return $categoryImageEntity;
                };

                $identifier = $entity->hasImage()
                    ? $entity->getImage()->getReferenceIdentifier()
                    : "categories/" . $this->categoryEntity->getId() . "/" . Strings::webalize($image->getName(), "_.");

                $imageEntity = $imageJob->createImageFromUpload($image, $identifier);
            }



            $entity->mergeNewTranslations();

            $this->catalogFacade->getEntityManager()->persist($form->getEntity())->flush();
            $this->flashMessage("Kategorie {$form->getId()} upravena", 'success');
            $this->ajaxRedirect();
        };

        return $form;
    }





}