<?php
/**
 * This file is part of affiliate-bagin.cz.
 * Copyright (c) 2019
 *
 * @file    FeedPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CmsModule\CatalogModule\Presenters;

use Devrun\CatalogModule\Managers\IFeedManage;
use Devrun\CmsModule\CatalogModule\Facades\FeedFacade;
use Devrun\CmsModule\Controls\DataGrid;
use Devrun\CmsModule\Presenters\AdminPresenter;
use Tracy\Debugger;

/**
 * Class FeedPresenter
 * @package Devrun\CmsModule\CatalogModule\Presenters
 */
class FeedPresenter extends AdminPresenter
{

    /** @var string */
    protected $feedXmlUrl;

    /** @var FeedFacade @inject */
    public $feedFacade;


    /**
     * DI setter
     * only template info
     *
     * @param mixed $feedXmlUrl
     */
    public function setFeedXmlUrl($feedXmlUrl)
    {
        $this->feedXmlUrl = $feedXmlUrl;
    }



    public function renderDefault()
    {
        $this->template->feedUrl = $this->feedXmlUrl;
    }



    public function handleSynchronize($url)
    {
        $this->feedFacade->getFeedManager()->synchronize($url);;
    }


    /**
     * @throws \Exception
     */
    public function handleUpdate()
    {
        $this->feedFacade->update();
        try {
            $this->flashMessage('Updatováno', 'info');

        } catch (\Exception $e) {
            $this->flashMessage("Nepodařilo se updatovat {$e->getMessage()}", 'danger');
            Debugger::log($e);
        }

        $this->ajaxRedirect();
    }



    /**
     * @param DataGrid $grid
     */
    protected function createDataGrid(DataGrid $grid)
    {
        $grid->useHappyComponents(true);
    }


    /**
     * @param DataGrid $grid
     */
    protected function createDataGridColumns(DataGrid $grid)
    {
        $grid->addColumnLink('id', 'ID', 'detail')
             ->setSortable()
             ->setFilterText();
    }


    /**
     * @param DataGrid $grid
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    protected function createDataGridActions(DataGrid $grid)
    {
        $grid->addAction('synchronize', 'Synchronize', 'synchronize!', ['url'])
             ->setIcon('american-sign-language-interpreting')
             ->setClass('_ajax btn btn-xs btn-primary');
    }


    /**
     * @param $name
     * @return \Devrun\CmsModule\Controls\DataGrid
     * @throws \Ublaboo\DataGrid\Exception\DataGridException
     */
    protected function createComponentFeedGridControl($name)
    {
        $grid = $this->createGrid($name);

        $this->createDataGrid($grid);
        $grid->setDataSource($this->getDataSource());
        $this->createDataGridColumns($grid);
        $this->createDataGridActions($grid);

        $grid->addToolbarButton('update!', 'Update');
        return $grid;
    }


    /**
     * @return array
     */
    protected function getDataSource()
    {
        return $this->feedFacade->getFeedManager()->getDataSource();
    }

}