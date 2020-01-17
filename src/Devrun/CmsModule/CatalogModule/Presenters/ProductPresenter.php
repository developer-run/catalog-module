<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    ProductPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CmsModule\CatalogModule\Presenters;

use Devrun\Application\UI\Presenter\TImgStoragePipe;
use Devrun\CatalogModule\Entities\CategoryEntity;
use Devrun\CatalogModule\Entities\ProductEntity;
use Devrun\CatalogModule\Entities\ProductImageEntity;
use Devrun\CmsModule\CatalogModule\Facades\CatalogFacade;
use Devrun\CmsModule\CatalogModule\Facades\CatalogImageFacade;
use Devrun\CmsModule\Controls\FlashMessageControl;
use Devrun\CmsModule\Facades\ImageManageFacade;
use Devrun\CmsModule\Forms\DevrunForm;
use Devrun\CmsModule\Presenters\AdminPresenter;
use Devrun\Utils\Pattern;
use Kdyby\Doctrine\QueryBuilder;
use Nette\Forms\Container;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Nette\Http\Request;
use Nette\Utils\Html;
use Nette\Utils\Strings;

class ProductPresenter extends AdminPresenter
{

    use TImgStoragePipe;

    /** @var int @persistent */
    public $categoryId;


    /** @var CatalogFacade @inject */
    public $catalogFacade;

    /** @var ImageManageFacade @inject */
    public $imageManageFacade;

    /** @var CatalogImageFacade @inject */
    public $catalogImageFacade;

    /** @var Request @inject */
    public $requestUrl;


    /** @var CategoryEntity */
    private $categoryEntity;

    /** @var ProductEntity */
    private $productEntity;

    /** @var ProductImageEntity */
    private $imageEntity;



    public function handleTopProduct($id)
    {
        /** @var ProductEntity $productEntity */
        if (!$productEntity = $this->catalogFacade->getProductRepository()->find($id)) {
            $this->flashMessage("Produkt $id nenalezen ", 'danger');
            $this->ajaxRedirect(':Cms:Catalog:Default:');
        }

        $productEntity->topped = !$productEntity->topped;

        $this->catalogFacade->getEntityManager()->persist($productEntity)->flush();

        $message = "Produkt " . ($productEntity->topped ? 'TOP' : 'odstraněn z TOP');
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, "Správa projektu", FlashMessageControl::TOAST_INFO);

        $this['productsGridControl']->redrawItem($id);
        $this->ajaxRedirect('this', null, ['flash']);
    }


    public function handleRecommendProduct($id)
    {
        /** @var ProductEntity $productEntity */
        if (!$productEntity = $this->catalogFacade->getProductRepository()->find($id)) {
            $this->flashMessage("Produkt $id nenalezen ", 'danger');
            $this->ajaxRedirect(':Cms:Catalog:Default:');
        }

        $productEntity->recommend = !$productEntity->recommend;

        $this->catalogFacade->getEntityManager()->persist($productEntity)->flush();

        $message = "Produkt " . ($productEntity->recommend ? 'doporučen' : 'odstraněn z doporučujeme');
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, "Správa projektu", FlashMessageControl::TOAST_INFO);

        $this['productsGridControl']->redrawItem($id);
        $this->ajaxRedirect('this', null, ['flash']);
    }


    public function handleActionProduct($id)
    {
        /** @var ProductEntity $productEntity */
        if (!$productEntity = $this->catalogFacade->getProductRepository()->find($id)) {
            $this->flashMessage("Produkt $id nenalezen ", 'danger');
            $this->ajaxRedirect(':Cms:Catalog:Default:');
        }

        $productEntity->action = !$productEntity->action;

        $this->catalogFacade->getEntityManager()->persist($productEntity)->flush();

        $message = "Produkt " . ($productEntity->action ? 'akce' : 'odstraněn z akce');
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, "Správa projektu", FlashMessageControl::TOAST_INFO);

        $this['productsGridControl']->redrawItem($id);
        $this->ajaxRedirect('this', null, ['flash']);
    }


    public function handleSetProductMainImage($id, $imageId)
    {
        /** @var ProductEntity $productEntity */
        if (!$productEntity = $this->catalogFacade->getProductRepository()->find($id)) {
            $this->flashMessage("Produkt $id nenalezen ", 'danger');
            $this->ajaxRedirect(':Cms:Catalog:Default:');
        }

        /** @var ProductImageEntity $imageEntity */
        if (!$imageEntity = $this->catalogFacade->getProductImageRepository()->find($imageId)) {
            $this->flashMessage("Obrázek $imageId nenalezen ", 'danger');
            $this->ajaxRedirect();
        }


        $this->catalogImageFacade->setProductMainImage($productEntity, $imageEntity);

        $message = "Obrázek $imageId nastaven jako hlavní ";
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, "Správa objednávek", FlashMessageControl::TOAST_INFO);

        $this->ajaxRedirect('this', null, ['flash']);
//        $this->ajaxRedraw();

    }


    public function handleImageDelete($imageId)
    {
        if (!$imageEntity = $this->catalogFacade->getProductImageRepository()->find($imageId)) {
            $this->flashMessage("Obrázek $imageId nenalezen ", 'danger');
            $this->ajaxRedirect();
        }

        $this->catalogImageFacade->removeImage($imageId);

        $message = "Obrázek $imageId smazán ";
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, "Správa objednávek", FlashMessageControl::TOAST_INFO);
        $this->ajaxRedirect();
    }



    public function actionEdit($id)
    {
        if (!$this->productEntity = $this->catalogFacade->getProductRepository()->find($id)) {
            $this->flashMessage("Produkt $id nenalezen ", 'danger');
            $this->ajaxRedirect(':Cms:Catalog:Default:');
        }

    }


    public function renderEdit($id)
    {
        $images = $this->catalogFacade->getProductImageRepository()->findBy(['product' => $this->productEntity]);

        $this->template->categoryId = $this->categoryId;
        $this->template->images = $images;
        $this->template->product = $this->productEntity;
    }



    public function actionImageEdit($id)
    {
        if (!$this->imageEntity = $this->catalogImageFacade->getProductImageRepository()->find($id)) {
            $this->flashMessage("Obrázek $id nenalezen ", 'danger');
            $this->ajaxRedirect(':Cms:Catalog:Default:');
        }

        $this->template->image = $this->imageEntity;
    }


    public function actionAddToCategory($id)
    {
        if (!$this->categoryEntity = $this->catalogFacade->getCategoryRepository()->find($id)) {
            $this->flashMessage("Kategorie $id nenalezena ", 'danger');
            $this->ajaxRedirect(':Cms:Catalog:Default:');
        }

        $productEntity = new ProductEntity();
        $productEntity->addCategory($this->categoryEntity);
        $this->productEntity = $productEntity;

        $this->setView('edit');
    }


    protected function createComponentProductForm($name)
    {
        $form = $this->catalogFacade->productFormFactory->create();

        $form
            ->bindEntity($this->productEntity)
            ->setDefaults([
                'name' => $this->productEntity->getName(),
                'title' => $this->productEntity->getTitle(),
                'description' => $this->productEntity->getDescription(),
            ])->onSuccess[] = function (DevrunForm $form, $values) {

            /** @var ProductEntity $entity */
            $entity = $form->getEntity();

            $ignore = ['categories'];
            foreach ($values as $key => $value) {
                if (!in_array($key, $ignore)) {
                    if (isset($entity->$key)) {
                        $entity->$key = $value;
                    }
                }
            }

            $entity->mergeNewTranslations();
            $this->catalogFacade->getEntityManager()->persist($entity)->flush();

            $this->flashMessage("Produkt {$form->getId()} upraven", 'success');
            $this->ajaxRedirect();
        };

        return $form;
    }


    protected function createComponentImagesForm($name)
    {
        $form = $this->catalogFacade->imagesFormFactory->create();

        $form
            ->bindEntity($this->productEntity)
            ->setDefaults([

            ])->onSuccess[] = function (DevrunForm $form, $values) {


//            dump($values);

            /** @var ProductEntity $entity */
            $entity = $form->getEntity();

            /** @var FileUpload[] $images */
            $images = $values->filenames;

            $imageEntities = [];

            $imageJob = $this->imageManageFacade->getImageJob()
                ->setImageRepository($this->catalogFacade->getProductImageRepository());

            $imageJob->callCreateImageEntity = function ($referenceIdentifier) {
                return new ProductImageEntity($this->productEntity, $referenceIdentifier);
            };

            foreach ($images as $image) {
                if ($image->isOk() && $image->isImage()) {
                    $identifier = "products/" . $this->productEntity->getId() . "/" . Strings::webalize($image->getName(), "_.");
                    $imageEntities[] = $imageJob->createImageFromUpload($image, $identifier);
                }
            }

            if ($imageEntities) {
                $this->catalogFacade->getEntityManager()->flush();
                //$this->catalogImageFacade->garbageImages($entity);

                $this->flashMessage("Obrázky produktu {$form->getId()} nahrány", 'success');
                $this->ajaxRedirect();
            }
        };

        return $form;
    }


    protected function createComponentImageForm($name)
    {
        $form = $this->catalogFacade->imageFormFactory->create();

        $form
            ->bindEntity($this->imageEntity)
            ->setDefaults([

            ])->onSuccess[] = function (DevrunForm $form, $values) {

            /** @var ImagesEntity $entity */
            $entity     = $form->getEntity();
            $imageReady = false;

            /** @var FileUpload $image */
            if ($image = $values->imageUpload) {
                if ($image->isOk() && $image->isImage()) {
                    $imageReady = true;
                }
            }


            if ($imageReady)
                $this->imageManageFacade->updateImageFromEntity($entity, $image);

            else
                $this->imageManageFacade->imageRepository->getEntityManager()->persist($entity)->flush();

            $this->flashMessage("Obrázek {$entity->getId()} upraven", 'success');

            if ($this->isAjax()) $this->redrawControl();
            else {
                $this->redirect('edit', $entity->getProduct()->getId());
            }


        };

        return $form;
    }


    protected function createComponentProductsGridControl($name)
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




        $query = $this->catalogFacade->getProductRepository()->createQueryBuilder('e')
            ->addSelect('i')
            ->addSelect('u')
            ->leftJoin('e.categories', 'c')
            ->leftJoin('e.images', 'i')
            ->leftJoin('e.deletedBy', 'u')
//            ->andWhere('c.id = :category')->setParameter('category', $this->categoryEntity->getId())
        ;


        $grid->setDataSource($query);

        $grid->addColumnLink('photo', '')
            ->setFitContent()
            ->setRenderer(function (ProductEntity $productEntity) use ($presenter) {

                /** @var ProductImageEntity $image */
                if (!$image = $productEntity->getMainPhoto()) {
                    $image = new ProductImageEntity($productEntity, 'products/id/unknown.png');
                }

                $img = $presenter->imgStorage->fromIdentifier([$image->getIdentifier(), '70x30']);
                $html = Html::el('img')->setAttribute('src', $this->requestUrl->getUrl()->getBasePath() . $img->createLink());
                return $html;

            });

        $grid->setColumnsHideable();

        $grid->addColumnDateTime('inserted', 'Vloženo')
            ->setSortable()
            ->setDefaultHide()
            ->setFilterDate();

        $grid->addColumnText('deletedBy', 'Smazáno')
            ->setSortable(true)
            ->setSortableCallback(function (QueryBuilder $queryBuilder, $value) {
                $queryBuilder->addOrderBy('u.username', $value['deletedBy']);
            })
            ->setDefaultHide()
            ->setFilterText();
        ;


        $grid->addColumnLink('name', 'Název', 'Product:edit')
//            ->addParameters(['categoryId' => $this->template->category->id])
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


        $grid->addAction('top', 'top', 'topProduct!')
            ->setIcon('hand-pointer-o')
            ->setTitle('Označit produkt `TOP`')
            ->setSortable(true)
            ->setClass(function (ProductEntity $entity) {
                return $entity->topped ? "btn btn-xs btn-info ajax" : "btn btn-xs btn-default ajax";
            });


        $grid->addAction('recommend', 'dop', 'recommendProduct!')
            ->setIcon('hand-spock-o')
            ->setSortable(true)
            ->setTitle('Označit produkt `doporučujeme`')
            ->setClass(function (ProductEntity $entity) {
                return $entity->recommend ? "btn btn-xs btn-info ajax" : "btn btn-xs btn-default ajax";
            });


        $grid->addAction('action', 'akce', 'actionProduct!')
            ->setIcon('handshake-o')
            ->setTitle('Označit produkt `akční`')
            ->setConfirm('Do you really want to delete row %s?', 'name')
            ->setClass(function (ProductEntity $entity) {
                return $entity->action ? "btn btn-xs btn-info ajax" : "btn btn-xs btn-default ajax";
            });


        $grid->addFilterMultiSelect('action', 'Search:', ['action', 'id'], 'action')
            ->setPlaceholder('Search...');


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


//        $grid->addAction('deleteProduct', 'Smazat', 'deleteProduct!')
//            ->addParameters(['cID' => $this->categoryEntity->id])
//            ->setRenderer(function (ProductEntity $entity) {
//                $href = Html::el('a')->setText('Smazat')->href($this->link('deleteProduct!', ['id' => $this->categoryEntity->getId(), 'pID' => $entity->getId()]));
//                $href->setAttribute('class', 'btn btn-xs btn-danger');
//
//                return $href;
//            })
//            ->setIcon('trash')
//            ->setClass('_ajax btn btn-xs btn-danger')
//            ->setConfirm(function ($item) {
//                return "Opravdu chcete smazat produkt [id: {$item->id} {$item->name}]?";
//            });


//        $grid->addGroupAction('Aktivní')->onSelect[] = [$this, 'setActives'];

        $grid->addToolbarButton(':Cms:Catalog:Default:', 'Kategorie');
//            ->setClass('btn btn-xs btn-info');



        return $grid;
    }




}