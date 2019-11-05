<?php
/**
 * This file is part of catalog.
 * Copyright (c) 2018
 *
 * @file    CategoryTranslationEntity.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Entities;

use Doctrine\ORM\Mapping as ORM;
use Devrun\Doctrine\Entities\Attributes\Translation;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class ProductTranslationEntity
 *
 * @ORM\Cache(region="category_translation")
 * @ORM\Entity
 * @ORM\Table(name="catalog_category_translation",
 * indexes={
 *  @ORM\Index(name="catalog_title_description_idx", columns={"title", "description"}, flags={"fulltext"}),
 * })
 *
 * @package Devrun\CatalogModule\Entities
 */
class CategoryTranslationEntity
{

    use Translation;
    use MagicAccessors;


    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true, length=128)
     */
    protected $title;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true, length=65536)
     */
    protected $description;






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








}