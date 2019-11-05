<?php
/**
 * This file is part of affiliate-bagin.cz.
 * Copyright (c) 2019
 *
 * @file    FeedLog.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Entities;

use Devrun\Doctrine\Entities\DateTimeTrait;
use Devrun\Doctrine\Entities\IdentifiedEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class FeedLog
 * @ORM\Entity
 * @ORM\Table(name="catalog_feed_log")
 *
 * @package Devrun\CatalogModule\Entities
 */
class FeedLog
{

    use IdentifiedEntityTrait;
    use MagicAccessors;
    use DateTimeTrait;


    /**
     * @var ProductEntity
     * @ORM\ManyToOne(targetEntity="ProductEntity")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $product;


    /**
     * @var integer
     * @ORM\Column(type="string")
     */
    protected $text;

    /**
     * FeedLog constructor.
     *
     * @param ProductEntity $product
     * @param int           $text
     */
    public function __construct(ProductEntity $product, $text)
    {
        $this->product = $product;
        $this->text    = $text;
    }


}