<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    CategoryTreeControl.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Controls;

use Devrun\CatalogModule\Repositories\CategoryRepository;
use Doctrine\ORM\Query;
use Flame\Application\UI\Control;

interface ICategoryTreeControlFactory
{
    /** @return CategoryTreeControl */
    function create();
}

class CategoryTreeControl extends Control
{

    /** @var CategoryRepository @inject */
    public $categoryRepository;

    /** @var array */
    protected $options = array();

    /** @var Query */
    protected $query = NULL;

    private $defaultViewLink = "Catalogue:list";

    /** @var string */
    private $templateFile;


    public function render()
    {
        $template = $this->getTemplate();

        if (($query = $this->query) === NULL) {
            $query = $this->createQuery();
        }

        if (($options = $this->options) === array()) {
            $options = $this->createOptions();
        }

        if ($this->templateFile) {
            $nodes = $this->categoryRepository->buildTree($query->getArrayResult(), $options = array('decorate' => false));
            $template->setFile($this->templateFile);

        } else {
            $nodes = $this->categoryRepository->buildTree($query->getArrayResult(), $options);
        }

//        dump($nodes);
//        die("END");

        $template->nodes = $nodes;
        $template->render();
    }


    /**
     * @return Query
     */
    protected function createQuery()
    {
        $query = $this->categoryRepository->createQueryBuilder('a')
            ->where('a.active = true')
                ->addOrderBy('a.root')
                ->addOrderBy('a.lft')
            ->getQuery();

        return $query;
    }


    /**
     * setter Query
     *
     * @param Query $query
     *
     * @return $this
     */
    public function setQuery(Query $query)
    {
        $this->query = $query;
        return $this;
    }


    /**
     * @return array
     */
    protected function createOptions()
    {
        $options = array(
            'decorate'            => true,
            'rootOpen'            => '<ul class="list-group">',
            'childOpen'           => '<li class="list-group-item">',
            'representationField' => 'name',
            'nodeDecorator'       => function ($node) {
                return '<a href="' . $this->getPresenter()->link($this->defaultViewLink, $node['id']) . '">' . '<i class="fa fa-circle-o"></i>' . $node['name'] . '</a>';
            },
            'html'                => true
        );

        return $options;
    }


    /**
     * setter options
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }


    /**
     * @param string $defaultViewLink
     *
     * @return $this
     */
    public function setDefaultViewLink($defaultViewLink)
    {
        $this->defaultViewLink = $defaultViewLink;
        return $this;
    }


    /**
     * @param string $templateFile
     *
     * @return CategoryTreeControl
     */
    public function setTemplateFile(string $templateFile): CategoryTreeControl
    {
        $this->templateFile = $templateFile;
        return $this;
    }


}