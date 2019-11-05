<?php
/**
 * This file is part of pexeso-devrun.
 * Copyright (c) 2018
 *
 * @file    ProductTranslationEntity.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Entities;

use Devrun\Entities\UtilityEntity;
use Doctrine\ORM\Mapping as ORM;
use Devrun\Doctrine\Entities\Attributes\Translation;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class ProductTranslationEntity
 * @ORM\Cache(region="product_translation", usage="NONSTRICT_READ_WRITE")
 * @ORM\Entity
 * @ORM\Table(name="catalog_product_translation",
 * indexes={
 *  @ORM\Index(name="product_fulltext_idx", columns={"title", "name", "short_description", "description"}, flags={"fulltext"}),
 * })
 *
 * @package Devrun\CatalogModule\Entities
 */
class ProductTranslationEntity
{

    use Translation;
    use MagicAccessors;


    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true, length=128)
     */
    protected $title;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name = "?";

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true, length=65536)
     */
    protected $shortDescription;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true, length=65536)
     */
    protected $description; // UtilityEntity::demoText;






    /**
     * @return null|string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return null|string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @param null|string $shortDescription
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
    }








}