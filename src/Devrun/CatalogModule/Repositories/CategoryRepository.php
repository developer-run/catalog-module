<?php
/**
 * This file is part of the devrun2016
 * Copyright (c) 2017
 *
 * @file    CategoryRepository.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Repositories;

use Doctrine\DBAL\DBALException;
use Gedmo\Tree\Traits\Repository\ORM\NestedTreeRepositoryTrait;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Doctrine\Mapping\ClassMetadata;
use PDO;

class CategoryRepository extends EntityRepository
{
    use NestedTreeRepositoryTrait;


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


    /**
     * @return int
     * @throws DBALException
     */
    public function getLastId()
    {
        $connection = $this->getEntityManager()->getConnection();
        $dbName     = $connection->getParams()['dbname'];
        $tableName  = $this->getClassMetadata()->getTableName();

        $sql = "SELECT `AUTO_INCREMENT` FROM  INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :dbName AND TABLE_NAME = :table";

        $params = [
            'dbName' => $dbName,
            'table'  => $tableName,
        ];

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute($params);

        return intval($stmt->fetch(PDO::FETCH_COLUMN));
    }


}