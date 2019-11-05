<?php
/**
 * This file is part of affiliate-bagin.cz.
 * Copyright (c) 2019
 *
 * @file    SearchControl.phpt
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Controls;

use Devrun\Application\UI\Presenter\TImgStoragePipe;
use Devrun\CatalogModule\Entities\CategoryEntity;
use Devrun\CatalogModule\Entities\ProductEntity;
use Devrun\CmsModule\CatalogModule\Facades\CatalogFacade;
use Flame\Application\UI\Control;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Form;
use Tracy\Debugger;

interface ISearchControlFactory
{
    /** @return SearchControl */
    function create();
}

/**
 * Class SearchControl
 *
 * @package Devrun\CatalogModule\Controls
 * @method onSearch(string $term)
 */
class SearchControl extends Control
{

    use TImgStoragePipe;

    /** @var CatalogFacade @inject */
    public $catalogFacade;

    /** @var array event */
    public $onSearch = [];


    public function render()
    {
        $template = $this->getTemplate();

        $template->render();
    }


    public function handleSearch($term)
    {
        $term   = $this->presenter->getParameter('term');
        $result = $this->fullTextSearch($term);

        $this->presenter->sendResponse(new JsonResponse($result));
    }

    public function handleSelect($id, $category, $term)
    {
//        dump($id);
//        dump($category);
//        dump($term);
//        die();
        $link = null;
        if ($category == 'product') {
//            $link = $this->presenter->link('Product:detail', ['id' => $id, 'term' => $term]);
//            $link = $this->presenter->link('Product:detail', ['id' => $id]);
//            $link = $this->presenter->link('Product:detail', $id);

//            $this->presenter->payload->redirects = $link;
//            $this->presenter->sendPayload();

            $this->presenter->redirect('Product:detail', $id);

        } elseif ($category == 'category') {
            $link = $this->presenter->link('Catalogue:list', ['id' => $id]);
        }

//        $this->presenter->term = $term;
//        $this->redrawControl();
//        $this->presenter->redrawControl();
//        if ($link) $this->presenter->redirectUrl($link);

    }



    private function fullTextSearch($term)
    {
        $match = 0.00;
        $mode = "expand";
        $mode = "boolean";
//        $mode = "NATURAL";
        $search = "cestovní taška";
//        $search = "deštník";

        $productRepository = $this->catalogFacade->getProductRepository();

        /** @var ProductEntity[] $rawResult */
        $rawResult = $productRepository->createQueryBuilder('e')
            ->addSelect('pt')
            ->addSelect('img')
            ->leftJoin('e.translations', 'pt')
            ->leftJoin('e.images', 'img')
//            ->addSelect("MATCH_AGAINST (pt.title, pt.name, pt.shortDescription, pt.description, :search 'IN NATURAL MODE') as matchAlias")
//            ->andWhere("MATCH_AGAINST(pt.title, pt.name, pt.shortDescription, pt.description, :search) > $match")
            ->addSelect("(MATCH (pt.title, pt.name, pt.shortDescription, pt.description) AGAINST (:search $mode) + MATCH (e.manufacturer) AGAINST (:search $mode) ) AS HIDDEN matchAlias")
            ->andWhere("(MATCH (pt.title, pt.name, pt.shortDescription, pt.description) AGAINST (:search $mode) + MATCH (e.manufacturer) AGAINST (:search $mode) ) > $match")

            ->addOrderBy('matchAlias', 'desc')
            ->setParameter('search', $term)
            ->setMaxResults(40)
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($rawResult as $item) {
            $mainPhoto = $item->getMainPhoto();
            $img = $this->imgStorage->fromIdentifier([$mainPhoto->getIdentifier(), '20x20']);
            $result[] = [
                'id' => $item->id,
                'value' => $item->getName(),
                'label' => $item->getName(),
                'category' => 'product',
                'img' => $img->createLink(),
                'link' => $this->presenter->link('Product:detail', $item->getId()),
            ];
        }

//        dump($result);
//        dump($rawResult);
//        die();











        $catalogRepository = $this->catalogFacade->getCategoryRepository();

//        /** @var CategoryEntity[] $rawResult */
//        $rawResult = $catalogRepository->createQueryBuilder('e')
//            ->leftJoin('e.products', 'p')
//            ->leftJoin('p.translations', 'pt')
//            ->addSelect("(MATCH (pt.title, pt.name, pt.shortDescription, pt.description) AGAINST (:search $mode) + MATCH (e.name) AGAINST (:search $mode) ) AS HIDDEN matchAlias")
//            ->andWhere("(MATCH (pt.title, pt.name, pt.shortDescription, pt.description) AGAINST (:search $mode) + MATCH (e.name) AGAINST (:search $mode) ) > $match")
//            ->addOrderBy('matchAlias', 'desc')
//            ->setParameter('search', $term)
//            ->getQuery()
//            ->getResult();

        /** @var CategoryEntity[] $rawResult */
        $rawResult = $catalogRepository->createQueryBuilder('e')
            ->addSelect("(MATCH (e.name) AGAINST (:search $mode) ) AS HIDDEN matchAlias")
            ->andWhere("(MATCH (e.name) AGAINST (:search $mode) ) > $match")
            ->addOrderBy('matchAlias', 'desc')
            ->setParameter('search', htmlspecialchars($term))
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

//        $result = [];
        foreach ($rawResult as $item) {
            $result[] = [
                'id' => $item->id,
                'value' => $item->getName(),
                'label' => $item->getName(),
                'category' => 'category' ,
                'img' => 'kategorka.png',
                'link' => $this->presenter->link('Catalogue:list', $item->getId()),
            ];
        }


//        Debugger::barDump($result);

        return $result;

        dump($result);
        die();

    }






    protected function createComponentFulltextSearch($name)
    {
        $form = new Form();

        $form->addText('term')
            ->getControlPrototype()
            ->setAttribute('id', 'headerSearch')
            ->setAttribute('type', 'Hledat')
            ->setAttribute('placeholder', 'Hledaný výraz, např. tašky');

        $form->addSubmit('send')
            ->getControlPrototype()->setAttribute('class', 'btn btn-default');

        $form->getElementPrototype()->setAttribute('class', 'site-block-top-search');

        $form->onSuccess[] = function ($form, $values) {
            $this->onSearch($values->term);
        };

        return $form;
    }


}