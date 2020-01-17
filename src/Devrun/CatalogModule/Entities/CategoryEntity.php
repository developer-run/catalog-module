<?php
/**
 * This file is part of the devrun2016
 * Copyright (c) 2017
 *
 * @file    CategoryEntity.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Entities;

use Devrun\CmsModule\Entities\BlameableTrait;
use Devrun\CmsModule\Entities\RouteEntity;
use Devrun\DoctrineModule\Entities\Attributes\Translatable;
use Devrun\DoctrineModule\Entities\DateTimeTrait;
use Devrun\DoctrineModule\Entities\IdentifiedEntityTrait;
use Devrun\DoctrineModule\Entities\NestedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Kdyby\Translation\Translator;

/**
 * Class CategoryEntity
 * @ORM\Cache(region="category", usage="NONSTRICT_READ_WRITE")
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Devrun\CatalogModule\Repositories\CategoryRepository")
 * @ORM\Table(name="catalog_category",
 * indexes={
 *  @ORM\Index(name="catalog_category_name_idx", columns={"name"}, flags={"fulltext"}),
 *  @ORM\Index(name="catalog_active_idx", columns={"active"}),
 * })
 *
 * @package Devrun\CatalogModule\Entities
 * @method CategoryTranslationEntity translate($lang = '', $fallbackToDefault = true)
 * @method getActive()
 */
class CategoryEntity
{

    use IdentifiedEntityTrait;
    use MagicAccessors;
    use DateTimeTrait;
    use BlameableTrait;
    use Translatable;
    use NestedEntityTrait;


    /**
     * @var ProductEntity[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="ProductEntity", mappedBy="categories")
     */
    protected $products;


    /**
     * @var RouteEntity
     * @ORM\ManyToOne(targetEntity="Devrun\CmsModule\Entities\RouteEntity")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $route;

    /**
     * @var CategoryImageEntity
     * @ORM\OneToOne(targetEntity="CategoryImageEntity", inversedBy="category")
     */
    protected $image;


    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="CategoryEntity")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="CategoryEntity", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="CategoryEntity", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
     * @var int
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    protected $position = 0;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name = "?";

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $note;

    /**
     * @var array
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected $options = [];

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default": true})
     */
    protected $active = true;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $topped = false;


    /**
     * CategoryEntity constructor.
     *
     * @param Translator $translator
     * @param string     $name
     */
    public function __construct(Translator $translator, string $name)
    {
        $this->products = new ArrayCollection();

        $this->setDefaultLocale($translator->getDefaultLocale());
        $this->setCurrentLocale($translator->getLocale());
        $this->name = $name;
    }


    /**
     * @param CategoryEntity|null $parent
     *
     * @return $this
     */
    public function setParent(CategoryEntity $parent = null)
    {
        $this->parent = $parent;
        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }


    public function hasRoute(): bool
    {
        return $this->route !== null;
    }

    /**
     * @return RouteEntity
     */
    public function getRoute(): RouteEntity
    {
        return $this->route;
    }

    /**
     * @param RouteEntity $route
     *
     * @return CategoryEntity
     */
    public function setRoute(RouteEntity $route): CategoryEntity
    {
        $this->route = $route;
        return $this;
    }




    /**
     * @return ProductEntity[]|ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
    }


    /**
     * @param ProductEntity $productEntity
     *
     * @return $this
     */
    public function addProduct(ProductEntity $productEntity)
    {
        if (!$this->products->contains($productEntity)) {
            $this->products->add($productEntity);
        }
        return $this;
    }

    public function removeProduct(ProductEntity $productEntity)
    {
        if ($this->products->contains($productEntity)) {
            $this->products->remove($productEntity->id);
        }

        return $this;
    }


    /**
     * @return bool
     */
    public function hasImage(): bool
    {
        return $this->image == true;
    }


    /**
     * @return CategoryImageEntity
     */
    public function getImage(): CategoryImageEntity
    {
        return ($this->image == true)
            ? $this->image
            : new CategoryImageEntity($this, 'category/unknown.png');
    }

    /**
     * @param CategoryImageEntity $image
     *
     * @return $this
     */
    public function setImage(CategoryImageEntity $image)
    {
        $this->image = $image;
        return $this;
    }




    /**
     * @param array $productsEntity
     *
     * @return $this
     */
    public function addProducts(array $productsEntity)
    {
        $this->products[] = $productsEntity;
        return $this;
    }


    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }


    public function setTitle($name)
    {
        $this->translate($this->currentLocale, false)->setTitle($name);
        return $this;
    }

    public function getTitle()
    {
        return $this->translate()->getTitle();
    }


    public function setDescription($name)
    {
        $this->translate($this->currentLocale, false)->setDescription($name);
        return $this;
    }

    public function getDescription()
    {
        return $this->translate()->getDescription();
    }



    /**
     * @param bool $published
     *
     * @return CategoryEntity
     */
    public function setActive(bool $published): CategoryEntity
    {
        $this->active = $published;
        return $this;
    }

    /**
     * @param bool $topped
     *
     * @return $this
     */
    public function setTopped(bool $topped): CategoryEntity
    {
        $this->topped = $topped;
        return $this;
    }



    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return CategoryEntity
     */
    public function setOptions(array $options): CategoryEntity
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param string $note
     */
    public function setNote(string $note)
    {
        $this->note = $note;
    }






}