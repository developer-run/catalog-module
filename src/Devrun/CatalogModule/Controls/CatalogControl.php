<?php
/**
 * This file is part of affiliate-bagin.cz.
 * Copyright (c) 2019
 *
 * @file    CatalogControl.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Controls;

use Devrun\Application\UI\Presenter\TImgStoragePipe;
use Devrun\CatalogModule\Entities\CategoryEntity;
use Devrun\CatalogModule\Entities\ProductEntity;
use Devrun\CatalogModule\Entities\ProductVariantEntity;
use Devrun\CatalogModule\Repositories\CategoryRepository;
use Devrun\CatalogModule\Repositories\Filters\CatalogFilter;
use Devrun\CatalogModule\Repositories\ProductRepository;
use Devrun\CatalogModule\Repositories\ProductVariantRepository;
use Devrun\CatalogModule\Repositories\Queries\ProductQuery;
use Devrun\CatalogModule\Repositories\Queries\ProductVariantQuery;
use Flame\Application\UI\Control;
use Nette\Application\UI\Form;
use Tracy\Debugger;

interface ICatalogControlFactory
{
    /** @return CatalogControl */
    function create();
}

class CatalogControl extends \Devrun\Application\UI\Control\Control
{

    use TImgStoragePipe;

    /** @var ProductRepository @inject */
    public $productRepository;

    /** @var CategoryRepository @inject */
    public $categoryRepository;

    /** @var ProductVariantRepository @inject */
    public $productVariantRepository;

    /** @var CatalogFilter */
    private $filter;

    /** @var VariantList */
    private $variantList;

    /** @var string @persistent */
    public $sort;

    /** @var string|null @persistent */
    public $limit;

    /** @var array @persistent */
    public $variants;

    /** @var array @persistent */
    public $variantsOrder;

    /** @var int */
    private $categoryID;

    /** @var string */
    private $searchTerm;



    protected function attached($presenter)
    {
        parent::attached($presenter);
//        Debugger::barDump(__FUNCTION__);

        if ($presenter instanceof \Nette\Application\IPresenter) {
            $this->filter = new CatalogFilter();

            if ($this->categoryID) {
                /** @var CategoryEntity $category */
                $category = $this->categoryRepository->find($this->categoryID);
                $this->filter->setCategory($category);
            }

            $this->initFilter();

            $priceMin = $this->filter->getPriceMin();
            $priceMax = $this->filter->getPriceMax();

            if ($priceMin || $priceMax) {
                $priceRange = "{$priceMin}kč - {$priceMax}kč";
                $this["filterForm-price"]->setDefaultValue($priceRange);
            }

            if ($sortedSelectedVariants = $this->getSortedSelectVariants()) {
                foreach ($sortedSelectedVariants as $index => $default) {
                    $this["filterForm-variants-$index"]->setDefaultValue($default);
                }
            }

            $this['sortingForm-sort']->setDefaultValue($this->sort);
            $this->filter->setSort($this->sort);
        }

    }


    private function initFilter()
    {
        /*
         * init priceMin priceMax
         */
        $priceMin = $priceMax = 0;
        if ($this->limit !== null) {
            $limit = explode('-', $this->limit);
            $priceMin = $limit[0];
            $priceMax = $limit[1];
        }
        $this->filter->setPriceMin($priceMin)->setPriceMax($priceMax);


        /*
         * init variants
         */
        $variants = [];
        $this->initVariantList();
        if ($this->variants) {

            $sortedSelectedVariants = $this->getSortedSelectVariants();
            $variantList = $this->getVariantList();

            foreach ($sortedSelectedVariants as $idx => $sortedSelectedVariant) {
                foreach ($sortedSelectedVariant as $id) {
                    $variants[$idx][$id] = $variantList[$idx]->values[$id]['entity'];
                }
            }
        }
        $this->filter->setVariants($variants);

        /*
         * init sorting
         */
        $this->filter->setSort($this->sort);

        /*
         * init search term
         */
        $this->filter->setSearchTerm($this->searchTerm);
    }


    /**
     * @return CatalogFilter
     */
    public function getFilter(): CatalogFilter
    {
        return $this->filter;
    }

    /**
     *
     * @return array index => [ids], index => [ids] ...
     *
     * @example [ 0 => [85, 12], 5 => [5, 15, 18] ]
     */
    private function getSortedSelectVariants()
    {
        $defaults = [];
        if ($this->variants) {
            $variantList = $this->getVariantList();
            foreach ($variants = explode('|', $this->variants) as $id) {
                foreach ($variantList as $index => $list) {
                    if (isset($list->values[$id])) {
                        $defaults[$index][] = $id;
                        break;
                    }
                }
            }
        }

        return $defaults;
    }


    /**
     * @return VariantList[]
     */
    private function getVariantList()
    {
        if (null === $this->variantList) {
            $this->variantList = $this->initVariantList();
        }

        return $this->variantList;
    }


    /**
     * @return VariantList[]
     */
    private function initVariantList()
    {
        $filter   = $this->filter;
        $variants = $this->variants ? explode('|', $this->variants) : [];

        $query = (new ProductVariantQuery())
            ->inProductCategory($filter->getCategory())
            ->withProductsCount($variants, $filter->getPriceMin(), $filter->getPriceMax());



        $variantEntitiesCounts = $this->productVariantRepository->fetch($query)->getIterator();

//        dump($variantEntitiesCounts);
//        die();


//        Debugger::barDump($variantEntitiesCounts);

        $rawVariants = [];
        foreach ($variantEntitiesCounts as $idx => $variantCount) {

            /** @var ProductVariantEntity $variant */
            $variant  = $variantCount['entity'];
            $products = $variantCount['products'];

            $rawVariants[$variant->getName()][$variant->getId()] = ['name' => $variant->getValue(), 'entity' => $variant, 'count' => $products];
        }

        $index = 0;
        $variantList = [];
        foreach ($rawVariants as $name => $items) {

            $variantList[$index] = new VariantList($index, $name, $items);
            $index++;
        }

        return $this->variantList = $variantList;
    }






    /**
     * @return [ min, max ]
     */
    private function getLimit()
    {
        $query = $this->productRepository->createQueryBuilder('e')
            ->select('MIN(e.price) AS minimum, MAX(e.price) AS maximum');

        if ($category = $this->filter->getCategory()) {
            $query
                ->join('e.categories', 'c')
                ->where('c = :id')->setParameter('id', $this->filter->getCategory());
        }
        if ($term = $this->filter->getSearchTerm()) {
            $query
                ->join('e.translations', 'translations')
                ->andWhere('translations.title LIKE :term')
                ->orWhere('translations.name LIKE :term')
                ->orWhere('translations.description LIKE :term')
                ->orWhere('translations.shortDescription LIKE :term')
                ->setParameter('term', "%$term%");
        }

        $result = $query
            ->getQuery()
            ->getSingleResult();

        return $result;
    }


    public function handleResetFilter()
    {

        $this->filter->setVariants([])->setPriceMin(0)->setPriceMax(0);
        $this->variants = null;
        $this->limit = null;

        $this->initFilter();

        foreach ($this->getVariantList() as $index => $variantList) {
            foreach ($variantList->values as $id => $value) {
                $this["filterForm-variants-$index"]->setDefaultValue([]);
            }
        }


        if ($this->isAjax()) {
            $this->ajaxRedirect('this', null, ['filter', 'products', 'pagination']);

        } else {
            $this->redirect('this', [
                'variants' => null,
                'limit'    => null
            ]);
        }

    }


    protected function createComponentSortingForm()
    {
        $form = new Form();

        $form->addSelect('sort', null, [
            CatalogFilter::SORT_BY_DATE    => 'Podle data',
            CatalogFilter::SORT_BY_CHEAPEST => 'Od nejlevnějších',
            CatalogFilter::SORT_BY_DEAREST => 'Od nejdražších',
        ]);
        $form->getElementPrototype()->setAttribute('class', 'ajax');

        $form->onSuccess[] = function ($form, $values) {
            if ($this->isAjax()) {

                $this->sort = $values->sort;
                $this->initFilter();
                $this->ajaxRedirect('this', null, ['filter', 'products', 'pagination']);

            } else {
                $this->redirect('this', [
                    'sort' => $values->sort,
                ]);
            }
        };

        return $form;
    }


    protected function createComponentFilterForm()
    {
//        Debugger::barDump(__FUNCTION__);


        $form = new Form();

        $form->addText('price')
            ->setAttribute('class', 'form-control border-0 pl-0 bg-white')
            ->setAttribute('id', 'amount-range')
            ->setAttribute('readonly', 'readonly');

        $variants = $form->addContainer('variants');
        $variantList = $this->getVariantList();

        foreach ($variantList as $index => $item) {
            $variants->addCheckboxList($index, $item->name, $item->getSelectItems());
//            Debugger::barDump($item->getSelectItems());
        }

        $form->addSubmit('filter', 'Filtrovat')
            ->getControlPrototype()->setAttribute('class', 'btn btn-xs btn-primary');

        $form->getElementPrototype()->setAttribute('class', 'ajax');

        $form->onSuccess[] = function ($form, $values) {

//            Debugger::barDump($values);

            /*
             * extract limit [150kč - 500kč]
             */
            if ($price = $values->price) {
                $priceExp = explode(' - ', $price);
                $min = intval($priceExp[0]);
                $max = intval($priceExp[1]);
                $this->limit = $limit = "$min-$max";

            } else {
                $limit = null;
            }

            $variantArray = [];
            foreach ($values->variants as $index => $variant) {
                if (!$variant) continue;
                $variantArray = array_merge($variantArray, $variant);
            }

            $this->variants = $variantArray ? implode('|', $variantArray) : null;


//            Debugger::barDump("SUCCESS");

            if ($this->isAjax()) {

//                $this->initVariantList();
                $this->initFilter();


                $this->ajaxRedirect('this', null, ['filter', 'products', 'pagination', 'itemsCount', 'slider']);
//                $this->presenter->redrawControl();
//                $this->presenter->payload->redirect = $this->link('this');

            } else {
                $this->redirect('this', [
                    'variants' => $variantImplode,
                    'limit'    => $limit
                ]);
            }


        };

        return $form;
    }



    public function render()
    {
//        Debugger::barDump(__FUNCTION__);

        $template = $this->getTemplate();

        $this['filterForm'];
        $this['sortingForm'];

        $limit    = $this->getLimit();
        $filter   = $this->getFilter();
        $priceMin = $this->filter->getPriceMin() ? $this->filter->getPriceMin() : intval($limit['minimum']);
        $priceMax = $this->filter->getPriceMax() ? $this->filter->getPriceMax() : intval($limit['maximum']);

        $template->categories = $categories = $this->categoryRepository->findAssoc([], 'id');
        $template->limitMin = intval($limit['minimum']);
        $template->limitMax = intval($limit['maximum']);
        $template->limitShow = $limit['minimum'] != $limit['maximum'];
        $template->filterMin = $priceMin;
        $template->filterMax = $priceMax;
        $template->variants = $this->getVariantList();

        $query = (new ProductQuery())->filtered($filter)
            ->withCategories()
            ->withImages()
            ->withVariants()
            ->withTranslations()
        ;

        /** @var ProductEntity[] $products */
        $resultSet = $this->productRepository->fetch($query);

        // Get visual pagination components
        $pagination = $this['pagination'];
        $pagination  = $pagination->getPaginator();
        $pagination->setItemsPerPage(12);

        // Apply limits to list
        $products = $resultSet->applyPaginator($pagination);

        $template->itemsTotal = $pagination->getItemCount();
        $template->path = $this->filter->getCategory() ? $this->categoryRepository->getPath($this->filter->getCategory()) : null;
        $template->products = $products;
        $template->render();
    }





    /**
     * Create items paginator
     *
     * @return \IPub\VisualPaginator\Components\Control
     */
    protected function createComponentPagination()
    {
        $that = $this;

        // Init visual paginator
        $control = new \IPub\VisualPaginator\Components\Control();
        $control
            ->enableAjax()
            ->setTemplateFile(__DIR__ . "/CatalogControlPagination.latte")
            ->onShowPage[] = (function ($component, $page) use ($that) {
                $this->ajaxRedirect('this', null, ['pagination', 'products']);
        });

        return $control;
    }


    /**
     * @param int $categoryID
     *
     * @return CatalogControl
     */
    public function setCategoryID(int $categoryID): CatalogControl
    {
        $this->categoryID = $categoryID;
        return $this;
    }

    /**
     * @param string $searchTerm
     *
     * @return CatalogControl
     */
    public function setSearchTerm(string $searchTerm): CatalogControl
    {
        $this->searchTerm = $searchTerm;
        return $this;
    }



}


/**
 * Class VariantList
 * [1 => [name], 1 => [values] ]
 *
 * @package Devrun\CatalogModule\Controls
 */
class VariantList {

    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /** @var array */
    public $values = [];

    /**
     * VariantList constructor.
     *
     * @param int   $id
     * @param array $name
     * @param array $values
     */
    public function __construct($id, $name, $values)
    {
        $this->id     = $id;
        $this->name   = $name;
        $this->values = $values;
    }


    /**
     * @return array
     */
    public function getSelectItemsWithCount()
    {
        $result = [];
        foreach ($this->values as $id => $value) {
            $result[$id] = $value['name'] . " ({$value['count']})";
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getSelectItems()
    {
        $result = [];
        foreach ($this->values as $id => $value) {
            $result[$id] = $value['name'];
        }
        return $result;
    }

    /**
     * @param $id
     *
     * @return int
     */
    public function getVariantProducts($id)
    {
        $return = isset($this->values[$id]['count']) ? intval($this->values[$id]['count']) : 0;

//        Debugger::barDump($id . ":" . $return);
        return $return;

    }

}
