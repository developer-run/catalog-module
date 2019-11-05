<?php
/**
 * This file is part of nivea-2019-klub-rewards.
 * Copyright (c) 2019
 *
 * @file    OrderRepository.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Repositories;

use Kdyby\Doctrine\EntityRepository;

class OrderRepository extends EntityRepository
{


    /**
     * @return int
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

        return intval($stmt->fetch(\PDO::FETCH_COLUMN));
    }



}