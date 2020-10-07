<?php
/**
 * This file is part of nivea-2019-klub-rewards.
 * Copyright (c) 2019
 *
 * @file    CategoryImage.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Entities;

use Devrun\CmsModule\Entities\IImage;
use Devrun\DoctrineModule\Entities\DateTimeTrait;
use Devrun\DoctrineModule\Entities\IdentifiedEntityTrait;
use Devrun\DoctrineModule\Entities\ImageTrait;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\MagicAccessors\MagicAccessors;

/**
 * Class CategoryImageEntity
 *
 * @ORM\Cache(region="category_image", usage="NONSTRICT_READ_WRITE")
 * @ORM\Entity(repositoryClass="Devrun\CatalogModule\Repositories\CategoryImageRepository")
 * @ORM\Table(name="catalog_category_image", indexes={
 *     @ORM\Index(name="catalog_category_identifier_idx", columns={"identifier"}),
 *     @ORM\Index(name="catalog_category_image_namespace_name_idx", columns={"namespace", "name"}),
 * })
 * @package Devrun\CatalogModule\Entities
 */
class CategoryImageEntity  implements IImage
{

    use IdentifiedEntityTrait;
    use MagicAccessors;
    use DateTimeTrait;
    use ImageTrait;

    /**
     * @var CategoryEntity
     * @ORM\OneToOne(targetEntity="CategoryEntity", mappedBy="image")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $category;

    /**
     * CategoryImageEntity constructor.
     *
     * @param CategoryEntity $category
     */
    public function __construct(CategoryEntity $category, $referenceIdentifier)
    {
        $this->setCategory($category);
        $this->setReferenceIdentifier($referenceIdentifier);
    }



    /**
     * @param CategoryEntity $category
     */
    public function setCategory(CategoryEntity $category)
    {
        $this->category = $category;
//        $category->setImage($this);
    }


    function __toString()
    {
        return $this->identifier;
    }


}