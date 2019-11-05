<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 31.1.17
 * Time: 13:38
 */

namespace Devrun\CatalogModule\Presenters;

use Devrun\CatalogModule\Controls\ICategoryTreeControlFactory;
use Devrun\CatalogModule\Controls\IProductsControlFactory;
use Devrun\CatalogModule\Repositories\CategoryRepository;
use Devrun\CatalogModule\Repositories\ProductRepository;

trait CatalogPresenterTrait
{
//    use


    /** @var IProductsControlFactory @inject */
    public $productsControl;

    /** @var ICategoryTreeControlFactory @inject */
    public $categoryTreeControl;

    /** @var CategoryRepository @inject */
    public $categoryRepository;

    /** @var ProductRepository @inject */
    public $productRepository;



    /**
     * @return \Devrun\CatalogModule\Controls\ProductsControl
     */
    protected function createComponentProductsControl()
    {
        $control = $this->productsControl->create();

        return $control;
    }

    protected function createComponentCategoryTreeControl()
    {
        $control = $this->categoryTreeControl->create();

//        $control->setDefaultViewLink(':Front:Category:view');

        return $control;
    }



}