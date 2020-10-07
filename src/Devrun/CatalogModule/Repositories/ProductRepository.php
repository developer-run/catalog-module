<?php
/**
 * This file is part of the devrun2016
 * Copyright (c) 2017
 *
 * @file    ProductRepository.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Repositories;

use Devrun\CatalogModule\Repositories\Queries\ItemQuery;
use Devrun\DoctrineModule\Repositories\EntityRepositoryTrait;
use Kdyby\Doctrine\EntityRepository;

class ProductRepository extends EntityRepository
{

    use EntityRepositoryTrait;


    public function getItemsInCategories($categories = null)
    {
        $query = (new ItemQuery())
            ->inCategory($categories)
//            ->orderByName();
;


//        $this->addFiltered($filter, $query);

        return $this->fetch($query);

    }

}
