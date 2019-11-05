<?php
/**
 * This file is part of nivea-2019-klub-rewards.
 * Copyright (c) 2019
 *
 * @file    OrderPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CmsModule\CatalogModule\Presenters;

use Devrun\Application\UI\Presenter\TImgStoragePipe;
use Devrun\CatalogModule\Entities\OrderEntity;
use Devrun\CatalogModule\Entities\ProductImageEntity;
use Devrun\CatalogModule\Repositories\OrderRepository;
use Devrun\CmsModule\Controls\FlashMessageControl;
use Devrun\CmsModule\Presenters\AdminPresenter;
use Kdyby\Doctrine\QueryBuilder;
use Nette\Forms\Container;
use Nette\Forms\Form;
use Nette\Http\Request;
use Nette\Utils\Html;
use Nette\Utils\Validators;
use Tracy\Debugger;

class OrdersPresenter extends AdminPresenter
{

    use TImgStoragePipe;

    /** @var OrderRepository @inject */
    public $orderRepository;

    /** @var Request @inject */
    public $httpRequest;


    public function handleDelete($id)
    {
        /** @var OrderEntity $orderEntity */
        if (!$orderEntity = $this->orderRepository->find($id)) {
            $message = "Objednávka $id nenalezena";
            $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, "Správa objednávek", FlashMessageControl::TOAST_WARNING);
            $this->ajaxRedirect('this', null, ['flash']);
        }

        $this->orderRepository->getEntityManager()->remove($orderEntity)->flush();

        $message = "objednávka `{$orderEntity->getId()}` smazána";
        $this->flashMessage($message, FlashMessageControl::TOAST_TYPE, "Správa produktů", FlashMessageControl::TOAST_INFO);
        $this->ajaxRedirect();
    }



    protected function createComponentOrderGridControl($name)
    {
        $grid       = $this->createGrid($name);
        $repository = $this->orderRepository;
        $presenter  = $this;

        $query = $repository->createQueryBuilder('e')
            ->addSelect('p')
            ->addSelect('pt')
            ->addSelect('u')
            ->join('e.product', 'p')
            ->leftJoin('p.translations', 'pt')
            ->leftJoin('e.user', 'u')
            ->andWhere('e.deletedBy IS NULL');



        $grid->setDataSource($query);

        $grid->addColumnText('image', '')
            ->setFitContent()
            ->setRenderer(function (OrderEntity $orderEntity) use ($presenter) {

                if (!$image = $orderEntity->getProduct()->getMainPhoto()) {
                    $image = new ProductImageEntity($orderEntity->getProduct(), 'products/id/unknown.png');
                }

                $img = $presenter->imgStorage->fromIdentifier([$image->getIdentifier(), '70x20']);
                $html = Html::el('img')->setAttribute('src', $this->httpRequest->url->basePath . $img->createLink());
                return $html;

            });


        $grid->addColumnText('product', 'Product', 'product.name')
            ->setSortable()
            ->setSortableCallback(function (QueryBuilder $queryBuilder, $value) {
                $queryBuilder->addOrderBy('pt.name', $value['product.name']);
            })
            ->setFilterText()
            ->setCondition(function (QueryBuilder $qb, $value) {
                $qb->andWhere('pt.name LIKE :name')->setParameter('name', "%$value%");
            });


        $grid->addColumnText('user', 'Zákazník', 'user.hash')
            ->setSortable()
            ->setSortableCallback(function (QueryBuilder $queryBuilder, $value) {
                $queryBuilder->addOrderBy('u.hash', $value['user.hash']);
            })
            ->setFilterText();

        $grid->addColumnNumber('price', 'Cena')
            ->setSortable()
            ->setEditableInputType('text', ['class' => 'form-control'])
            ->setEditableCallback(function ($id, $newValue) use ($grid) {
                if (Validators::is($newValue, $validate = 'numeric')) {
                    if ($entity = $this->orderRepository->find($id)) {
                        $entity->price = $newValue;
                        $this->orderRepository->getEntityManager()->persist($entity)->flush();
                        $this['orderGridControl']->reload();
                        return true;
                    }
                }

                $message = "input not valid [$newValue != $validate]";
                return $grid->invalidResponse($message);
            })
            ->setFilterText();


        $grid->addColumnNumber('amount', 'Množství')
            ->setSortable()
            ->setEditableInputType('text', ['class' => 'form-control'])
            ->setEditableCallback(function ($id, $newValue) use ($grid) {
                if (Validators::is($newValue, $validate = 'numericint')) {
                    if ($entity = $this->orderRepository->find($id)) {
                        $entity->amount = $newValue;
                        $this->orderRepository->getEntityManager()->persist($entity)->flush();
                        $this['orderGridControl']->reload();
                        return true;
                    }
                }

                $message = "input not valid [$newValue != $validate]";
                return $grid->invalidResponse($message);
            })
            ->setFilterText();

        $grid->setColumnsSummary(['price', 'amount']);


        /*
         * edit
         * __________________________________________________
         */
        $grid->addInlineEdit()->setText('Edit')
            ->onControlAdd[] = function(Container $container) {

            $container->addText('price')
                ->setAttribute('placeholder', 'cena produktu')
                ->addRule(Form::FILLED)
                ->addRule(Form::FLOAT);

            $container->addText('amount')
                ->setAttribute('placeholder', 'kusů')
                ->addRule(Form::FILLED)
                ->addRule(Form::INTEGER)
                ->addRule(Form::RANGE, null, [1,PHP_INT_MAX]);
        };

        $grid->getInlineEdit()->onSetDefaults[] = function(Container $container, OrderEntity $item) {

            $container->setDefaults([
                'id' => $item->id,
                'price' => $item->price,
                'amount' => $item->amount,
            ]);
        };

        $grid->getInlineEdit()->onSubmit[] = function($id, $values) use ($presenter, $grid) {

            /** @var OrderEntity $entity */
            if ($entity= $this->orderRepository->find($id)) {
                $entity
                    ->setPrice($values->price)
                    ->setAmount($values->amount);

                $this->orderRepository->getEntityManager()->persist($entity)->flush();

                $message = "Objednávka [{$entity->getId()}] upravena!";
                $presenter->flashMessage($message, FlashMessageControl::TOAST_TYPE, 'Správa objednávek', FlashMessageControl::TOAST_INFO);

                $this['orderGridControl']->redrawItem($id);
                $this->ajaxRedirect('this', null, ['flash']);
            }
        };



        /*
         * delete
         * __________________________________________________
         */
        $grid->addAction('delete', 'Smazat', 'delete!')
            ->setIcon('trash')
            ->setClass('ajax btn btn-xs btn-danger')
            ->setConfirm(function ($item) {
                return "Opravdu chcete smazat objednávku [id: {$item->id}]?";
            });



        return $grid;

    }


}