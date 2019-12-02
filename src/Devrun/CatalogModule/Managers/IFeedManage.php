<?php


namespace Devrun\CatalogModule\Managers;


use Devrun\CatalogModule\Entities\ProductEntity;

interface IFeedManage
{

    /**
     * @return string xml content
     */
    public function readFeed();

    /**
     * @param string $url
     * @return string html
     */
    public function getFeedContent(string $url);

    /**
     * @return array
     */
    public function getDataSource();

    /**
     * @param $id
     * @return void
     */
    public function synchronize($id);

    /**
     * @param IFeed $feed
     * @return string hash object
     */
    public function getSerial(IFeed $feed);


}