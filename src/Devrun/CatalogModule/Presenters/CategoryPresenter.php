<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    CategoryPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Presenters;

use Devrun\Application\UI\Presenter\BasePresenter;
use Devrun\CatalogModule\Repositories\CategoryRepository;
use Devrun\CatalogModule\Repositories\ProductRepository;
use Devrun\CmsModule\Presenters\CmsPresenterTrait;

class CategoryPresenter extends BasePresenter
{
    use CmsPresenterTrait;
    use CatalogPresenterTrait;


    /** @var ProductRepository @inject */
    public $productRepository;

    /** @var CategoryRepository @inject */
    public $categoryRepository;



    public function renderView($id)
    {
        if ($categoryEntity = $this->categoryRepository->find($id)) {

            foreach ($categoryEntity->products as $product) {
//                dump($product);
            }

//            dump($categoryEntity);

        }

    }




    protected function createComponentProductsControl()
    {
        $control = $this->productsControl->create();

        return $control;
    }


}