<?php
/**
 * This file is part of affiliate-bagin.cz.
 * Copyright (c) 2019
 *
 * @file    FeedPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CmsModule\CatalogModule\Presenters;

use Devrun\CmsModule\Presenters\AdminPresenter;

class FeedPresenter extends AdminPresenter
{

    public function renderDefault()
    {

        $file = "http://www.bagin.cz/eshop-export-heureka.xml";

        $tempDir = $this->context->parameters['tempDir'];
        $feedFile = $tempDir . "/feeds/feed.xml";


        if (!file_exists($feedFile)) {
            $fileContent = file_get_contents($file);

            if (!is_dir($dir = $tempDir . '/feeds')) {
                mkdir($dir);
            }

            file_put_contents($feedFile, $fileContent);
        }


        $xmlstring = file_get_contents($feedFile);


        $xml = simplexml_load_string($xmlstring);
        $json = json_encode($xml);
        $array = json_decode($json,TRUE);


        dump($array);
        die();

    }

}