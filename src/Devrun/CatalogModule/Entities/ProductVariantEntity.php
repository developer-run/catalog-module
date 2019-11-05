<?php
/**
 * This file is part of nivea-2019-klub-rewards.
 * Copyright (c) 2019
 *
 * @file    ProductVariantEntity.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Entities;

use Devrun\Doctrine\Entities\IdentifiedEntityTrait;
use Devrun\Doctrine\Entities\NestedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class ProductVariantEntity
 * @ORM\Cache(region="variants")
 * @ORM\Entity(repositoryClass="Devrun\CatalogModule\Repositories\ProductVariantRepository")
 * @ORM\Table(name="catalog_product_variant",
 * indexes={
 *  @ORM\Index(name="product_variant_name_idx", columns={"name"}),
 *  @ORM\Index(name="product_variant_value_idx", columns={"value"}),
 * })
 *
 * @package Devrun\CatalogModule\Entities
 * @method getName()
 * @method getValue()
 */
class ProductVariantEntity
{

    use MagicAccessors;
    use IdentifiedEntityTrait;


    /**
     * @var ProductEntity[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="ProductEntity", mappedBy="variants")
     */
    protected $products;


    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $value;


    /**
     * ProductVariantEntity constructor.
     */
    public function __construct($name, $value)
    {
        $this->name     = $name;
        $this->value    = $value;
        $this->products = new ArrayCollection();
    }





}