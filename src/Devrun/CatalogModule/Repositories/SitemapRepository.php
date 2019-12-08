<?php

namespace Devrun\CatalogModule\Repositories;

use Nette\Utils\DateTime;

class SitemapRepository
{
    /** @var CategoryRepository */
    private $catalogRepository;

    /** @var ProductRepository */
    private $productRepository;

    /** @var ProductVariantRepository */
    private $productVariantRepository;


    protected $catalogPresenter = "Homepage";
    protected $catalogAction = "default";

    protected $productPresenter = "Homepage";
    protected $productAction = "default";



    public function __construct(
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        ProductVariantRepository $productVariantRepository)
    {
        $this->catalogRepository        = $categoryRepository;
        $this->productRepository        = $productRepository;
        $this->productVariantRepository = $productVariantRepository;
    }


    public function getSitemap()
    {
        $result     = array();
        $categories = $this->catalogRepository->findBy(array());
        $products   = $this->productRepository->findBy(array(), null);
        $variants   = $this->productVariantRepository->findBy(array());


        $result[] = array(
            'presenter' => 'Homepage',
            'action'    => 'default',
            'id'        => null,
            'updated'   => new DateTime(),
        );

        foreach ($categories as $category) {
            $result[] = array(
                'presenter' => $this->catalogPresenter,
                'action'    => $this->catalogAction,
                'id'        => $category->id,
                'updated'   => $category->updated,
            );
        }

        foreach ($products as $product) {
            $result[] = array(
                'presenter' => $this->productPresenter,
                'action'    => $this->productAction,
                'id'        => $product->id,
                'updated'   => $product->updated,
            );
        }

/*        foreach ($articles as $article) {
            $result[] = array(
                'presenter' => 'Homepage',
                'action'    => 'article',
                'id'        => $article->id,
                'updated'   => $article->updated,
            );
        }*/


        return $result;
    }

    /**
     * @param string $catalogPresenter
     *
     * @return SitemapRepository
     */
    public function setCatalogPresenter(string $catalogPresenter): SitemapRepository
    {
        $this->catalogPresenter = $catalogPresenter;
        return $this;
    }

    /**
     * @param string $productPresenter
     *
     * @return SitemapRepository
     */
    public function setProductPresenter(string $productPresenter): SitemapRepository
    {
        $this->productPresenter = $productPresenter;
        return $this;
    }

    /**
     * @param string $catalogAction
     *
     * @return SitemapRepository
     */
    public function setCatalogAction(string $catalogAction): SitemapRepository
    {
        $this->catalogAction = $catalogAction;
        return $this;
    }

    /**
     * @param string $productAction
     *
     * @return SitemapRepository
     */
    public function setProductAction(string $productAction): SitemapRepository
    {
        $this->productAction = $productAction;
        return $this;
    }





}