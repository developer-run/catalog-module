<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    ProductPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Presenters;

use Devrun\Application\UI\Presenter\BasePresenter;
use Devrun\CmsModule\Presenters\CmsPresenterTrait;

class ProductPresenter extends BasePresenter
{
    use CmsPresenterTrait;
    use CatalogPresenterTrait;


    public function renderDetail($id)
    {
        if ($productEntity = $this->productRepository->find($id)) {


//            dump($productEntity);
//            die();



            $this->template->product = $productEntity;
        }
    }


}