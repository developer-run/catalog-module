<?php
/**
 * This file is part of the devrun2016
 * Copyright (c) 2017
 *
 * @file    CategoryRepository.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Repositories;

use Devrun\DoctrineModule\Repositories\EntityRepositoryTrait;
use Gedmo\Tree\Traits\Repository\ORM\NestedTreeRepositoryTrait;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Doctrine\Mapping\ClassMetadata;

class CategoryRepository extends EntityRepository
{
    use NestedTreeRepositoryTrait;
    use EntityRepositoryTrait;

    /**
     * CategoryRepository constructor.
     * @param EntityManager $em
     * @param ClassMetadata $class
     */
    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->initializeTreeRepository($em, $class);
    }



}