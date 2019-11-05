<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 31.1.17
 * Time: 13:19
 */

namespace Devrun\CatalogModule\Controls;

use Brabijan\Images\TImagePipe;
use Devrun\CatalogModule\Repositories\ProductRepository;
use Flame\Application\UI\Control;

interface IProductsControlFactory
{
    /** @return ProductsControl */
    function create();
}

class ProductsControl extends Control
{

    /** @var ProductRepository @inject */
    public $productRepository;


    public function render()
    {

        $template = $this->getTemplate();


//        $products = $this->productRepository->findBy(['published' => true, 'deletedBy' => null]);

        $products = $this->productRepository->createQueryBuilder('e')
            ->leftJoin('e.photos', 'p')
            ->orderBy('e.inserted')
            ->addOrderBy('p.head')
            ->getQuery()
            ->getResult();


//        dump($products);
//        die();



//        dump($products);
//        die();



        $template->products = $products;
        $template->render();
    }


    public function renderAction()
    {
        $template = $this->getTemplate();

        $template->render();
    }





}