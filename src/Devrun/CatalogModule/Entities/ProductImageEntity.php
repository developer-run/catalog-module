<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    ImagesEntity.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Entities;

use Devrun\CmsModule\Entities\IImage;
use Devrun\DoctrineModule\Entities\DateTimeTrait;
use Devrun\DoctrineModule\Entities\IdentifiedEntityTrait;
use Devrun\DoctrineModule\Entities\ImageTrait;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class ImagesEntity
 * @ORM\Cache(region="product_images", usage="NONSTRICT_READ_WRITE")
 * @ORM\Entity(repositoryClass="Devrun\CatalogModule\Repositories\ProductImageRepository")
 * @ORM\Table(name="catalog_product_images", indexes={
 *     @ORM\Index(name="name_idx", columns={"name"}),
 *     @ORM\Index(name="namespace_idx", columns={"namespace"}),
 *     @ORM\Index(name="product_reference_identifier_idx", columns={"reference_identifier"}),
 *     @ORM\Index(name="product_identifier_idx", columns={"identifier"}),
 * })
 *
 * @package Devrun\CatalogModule\Entities
 */
class ProductImageEntity implements IImage
{

    use IdentifiedEntityTrait;
    use MagicAccessors;
    use DateTimeTrait;
    use ImageTrait;


    /**
     * @var ProductEntity
     * @ORM\ManyToOne(targetEntity="ProductEntity", inversedBy="images")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $product;


    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description;


    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $main = false;


    /**
     * ImagesEntity constructor.
     *
     * @param ProductEntity $product
     * @param string        $referenceIdentifier
     */
    public function __construct(ProductEntity $product, string $referenceIdentifier)
    {
        $this->product = $product;
        $this->setReferenceIdentifier($referenceIdentifier);
    }

    /**
     * @return ProductEntity
     */
    public function getProduct()
    {
        return $this->product;
    }


    /**
     * @return bool
     */
    public function isMain(): bool
    {
        return $this->main;
    }

    /**
     * @param bool $main
     *
     * @return ProductImageEntity
     */
    public function setMain(bool $main): ProductImageEntity
    {
        $this->main = $main;
        return $this;
    }





}