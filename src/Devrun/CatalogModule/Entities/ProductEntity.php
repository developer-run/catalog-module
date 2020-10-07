<?php
/**
 * This file is part of the devrun2016
 * Copyright (c) 2017
 *
 * @file    ProductEntity.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Entities;

use Devrun\CmsModule\Entities\BlameableTrait;
use Devrun\CmsModule\Entities\RouteEntity;
use Devrun\DoctrineModule\Entities\Attributes\Translatable;
use Devrun\DoctrineModule\Entities\DateTimeTrait;
use Devrun\DoctrineModule\Entities\IdentifiedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\MagicAccessors\MagicAccessors;
use Kdyby\Translation\Translator;
use Nette\Utils\DateTime;

/**
 * Class ProductEntity
 * @ORM\Entity(repositoryClass="Devrun\CatalogModule\Repositories\ProductRepository")
 * @ORM\Table(name="catalog_product",
 * indexes={
 *  @ORM\Index(name="product_manufacturer_idx", columns={"manufacturer"}, flags={"fulltext"}),
 *  @ORM\Index(name="product_active_idx", columns={"active"}),
 *  @ORM\Index(name="action_recommend_topped_idx", columns={"action", "recommend", "topped"}),
 * })
 *
 * @package Devrun\CatalogModule\Entities
 * @method ProductTranslationEntity translate($lang = '', $fallbackToDefault = true)
 * @method getInStock()
 * @method getAmount()
 * @method getPrice()
 */
class ProductEntity
{

    use IdentifiedEntityTrait;
    use MagicAccessors;
    use DateTimeTrait;
    use BlameableTrait;
    use Translatable;


    /**
     * @var CategoryEntity[]|ArrayCollection
     * _@_ORM\Cache(region="product", usage="NONSTRICT_READ_WRITE")
     * @ORM\ManyToMany(targetEntity="CategoryEntity", inversedBy="products", orphanRemoval=true)
     * @ORM\JoinTable(name="catalog_category_products")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    public $categories;


    /**
     * @var ProductImageEntity[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="ProductImageEntity", mappedBy="product")
     */
    protected $images;


    /**
     * @var RouteEntity
     * @ORM\ManyToOne(targetEntity="Devrun\CmsModule\Entities\RouteEntity")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $route;


    /**
     * @var ProductVariantEntity[]|ArrayCollection
     * _@_ORM\Cache(region="product", usage="NONSTRICT_READ_WRITE")
     * @ORM\ManyToMany(targetEntity="ProductVariantEntity", inversedBy="products")
     * @ORM\JoinTable(name="catalog_products_variants")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    public $variants;


    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $serial;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $feedUrl;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    protected $manufacturer;

    /**
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2, options={"default": 0})
     */
    protected $price = 0;

    /**
     * @var integer
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    protected $dph = 19;

    /**
     * @var integer sleva
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    protected $discount = 0;

    /**
     * @var integer
     * @ORM\Column(type="integer", options={"default": 0})
     */
    protected $inStock = 0;

    /**
     * @var integer
     * @ORM\Column(type="integer", options={"default": 1})
     */
    protected $amount = 1;

    /**
     * @var integer
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $deliveryDate;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $publishedFrom;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $publishedTo;

    /**
     * @var int
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    protected $position = 0;


    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default": true})
     */
    protected $active = true;

    /**
     * @var boolean is product in action
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $action = false;

    /**
     * @var boolean is product recommend
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $recommend = false;

    /**
     * @var boolean is product in top list
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $topped = false;


    /**
     * ProductEntity constructor.
     *
     * @param Translator $translator
     * @param string     $name
     */
    public function __construct(Translator $translator, string $name)
    {
        $this->setDefaultLocale($translator->getDefaultLocale());
        $this->setCurrentLocale($translator->getLocale());

        $this->images     = new ArrayCollection();
        $this->variants   = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->setName($name);
    }

    /**
     * @return string
     */
    public function getSerial(): string
    {
        return $this->serial ?: '';
    }

    /**
     * @param string $serial
     * @return ProductEntity
     */
    public function setSerial(string $serial)
    {
        $this->serial = hash('sha256', $serial);
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function isAction(): bool
    {
        return $this->action;
    }

    /**
     * @return bool
     */
    public function isRecommend(): bool
    {
        return $this->recommend;
    }

    /**
     * @return bool
     */
    public function isTopped(): bool
    {
        return $this->topped;
    }


    /**
     * @param ProductEntity|null $parent
     *
     * @return $this
     */
    public function setParent(ProductEntity $parent = null)
    {
        $this->parent = $parent;
        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }


    /**
     * @param CategoryEntity $categoryEntity
     *
     * @return $this
     */
    public function addCategory(CategoryEntity $categoryEntity)
    {
        if (!$this->categories->contains($categoryEntity)) {
            $this->categories->add($categoryEntity);
        }

        return $this;
    }

    public function removeFromCategory(CategoryEntity $categoryEntity)
    {
        if ($this->categories->contains($categoryEntity)) {
            $this->categories->remove($categoryEntity);
        }

        return $this;
    }

    /**
     * @param CategoryEntity[] $categoryEntities
     *
     * @return $this
     */
    public function setCategories(array $categoryEntities)
    {
        $assocCategories = [];
        foreach ($categoryEntities as $categoryEntity) {
            if (!$this->categories->contains($categoryEntity)) {
                $this->categories->add($categoryEntity);
            }
            $assocCategories[] = $categoryEntity->id;
        }

        foreach ($this->categories as $category) {
            if ($category->getId() && !in_array($category->getId(), $assocCategories)) {
                $this->categories->remove($category->getId());
            }
        }
        
        return $this;
    }


    public function addVariant(ProductVariantEntity $productVariantEntity)
    {
        if (!$this->variants->contains($productVariantEntity)) {
            $this->variants->add($productVariantEntity);
        }

        return $this;
    }

    public function removeVariant(ProductVariantEntity $productVariantEntity)
    {
        if ($this->variants->contains($productVariantEntity)) {
            $this->variants->remove($productVariantEntity);
        }

        return $this;
    }


    public function setVariants(array $productVariantEntities)
    {
        $assocVariants = [];
        foreach ($productVariantEntities as $productVariantEntity) {
            if (!$this->variants->contains($productVariantEntity)) {
                $this->variants->add($productVariantEntity);
            }
            $assocVariants[] = $productVariantEntity->id;
        }

        foreach ($this->variants as $variant) {
            if ($variant->getId() && !in_array($variant->getId(), $assocVariants)) {
                $this->variants->remove($variant->getId());
            }
        }

        return $this;
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
     * @return ProductEntity
     */
    public function setRoute(RouteEntity $route): ProductEntity
    {
        $this->route = $route;
        return $this;
    }


    /**
     * @return ProductImageEntity[]|ArrayCollection
     */
    public function getPhotos()
    {
        return $this->photos;
    }


    public function getPhoto()
    {
        return $this->photos
            ? $this->photos->first()
            : null;
    }


    public function getMainPhoto()
    {
        foreach ($this->images as $photo) {
            if ($photo->getMain()) return $photo;
        }

        return null;
    }


    public function setName($name)
    {
        $this->translate($this->currentLocale, false)->setName($name);
        return $this;
    }

    public function getName()
    {
        return $this->translate()->getName();
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


    public function setDescription($description)
    {
        $this->translate($this->currentLocale, false)->setDescription($description);
        return $this;
    }
    public function getDescription()
    {
        return $this->translate()->getDescription();
    }


    public function setShortDescription($description)
    {
        $this->translate($this->currentLocale, false)->setShortDescription($description ?: null);
        return $this;
    }
    public function getShortDescription()
    {
        return $this->translate()->getShortDescription();
    }


    public function setAttachment($name)
    {
        $this->translate($this->currentLocale, false)->setAttachment($name);
        return $this;
    }

    public function getAttachment()
    {
        return $this->translate()->getAttachment();
    }





    public function getFeedUrl(): string
    {
        return $this->feedUrl ? $this->feedUrl : 'unknown';
    }
    public function setFeedUrl(string $feedUrl): ProductEntity
    {
        $this->feedUrl = $feedUrl;
        return $this;
    }





    /**
     * @param bool $active
     *
     * @return ProductEntity
     */
    public function setActive(bool $active): ProductEntity
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @param int $inStock
     *
     * @return ProductEntity
     */
    public function setInStock(int $inStock): ProductEntity
    {
        $this->inStock = $inStock;
        return $this;
    }

    /**
     * @param int $amount
     *
     * @return ProductEntity
     */
    public function setAmount(int $amount): ProductEntity
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param DateTime $publishedFrom
     *
     * @return ProductEntity
     * @throws \Exception
     */
    public function setPublishedFrom($publishedFrom): ProductEntity
    {
        if ($publishedFrom) {
            if (is_string($publishedFrom)) {
                $publishedFrom = new DateTime($publishedFrom);
            }

            $this->publishedFrom = $publishedFrom;
        }

        return $this;
    }

    /**
     * @param DateTime $publishedTo
     *
     * @return ProductEntity
     * @throws \Exception
     */
    public function setPublishedTo($publishedTo): ProductEntity
    {
        if ($publishedTo) {
            if (is_string($publishedTo)) {
                $publishedTo = new DateTime($publishedTo);
            }

            if ($this->publishedFrom && $publishedTo < $this->publishedFrom) {
                $publishedTo = $this->publishedFrom;
            }

            $this->publishedTo = $publishedTo;
        }

        return $this;
    }




    /**
     * @return DateTime|null
     */
    public function getPublishedFrom()
    {
        return $this->publishedFrom;
    }

    /**
     * @return DateTime|null
     */
    public function getPublishedTo()
    {
        return $this->publishedTo;
    }

    
    
    /**
     * @param float $price
     *
     * @return ProductEntity
     */
    public function setPrice($price): ProductEntity
    {
        $this->price = floatval($price);
        return $this;
    }

    /**
     * @param int $dph
     *
     * @return ProductEntity
     */
    public function setDph($dph): ProductEntity
    {
        $this->dph = intval($dph);
        return $this;
    }


    /**
     * @param int $deliveryDate
     *
     * @return ProductEntity
     */
    public function setDeliveryDate(int $deliveryDate): ProductEntity
    {
        $this->deliveryDate = $deliveryDate;
        return $this;
    }

    /**
     * @param string $manufacturer
     *
     * @return ProductEntity
     */
    public function setManufacturer(string $manufacturer): ProductEntity
    {
        $this->manufacturer = $manufacturer;
        return $this;
    }

    

}