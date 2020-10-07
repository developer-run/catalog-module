<?php
/**
 * This file is part of affiliate-bagin.cz.
 * Copyright (c) 2019
 *
 * @file    TrackingLog.php4
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Entities;

use Devrun\CmsModule\Entities\RouteEntity;
use Devrun\DoctrineModule\Entities\DateTimeTrait;
use Devrun\DoctrineModule\Entities\IdentifiedEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\MagicAccessors\MagicAccessors;

/**
 * Class TrackingLogEntity
 *
 * @ORM\Entity
 * @ORM\Table(name="catalog_tracking",
 * indexes={
 *  @ORM\Index(name="order_session_idx", columns={"session_id"}),
 *  @ORM\Index(name="action_idx", columns={"action"}),
 *  @ORM\Index(name="message_idx", columns={"message"}),
 *  @ORM\Index(name="ip_idx", columns={"ip"}),
 * })
 * @package Devrun\CatalogModule\Entities
 */
class TrackingEntity
{

    const
        ACTION_PAGE_VIEW = 'paveView',
        ACTION_PRODUCT_DETAIL = 'productDetail',
        ACTION_CATEGORY_LIST = 'categoryList',
        ACTION_SEARCH = 'search',
        ACTION_BOOK = 'book';


    use IdentifiedEntityTrait;
    use MagicAccessors;
    use DateTimeTrait;


    /**
     * @var integer
     * @ORM\Column(type="string", length=128)
     */
    protected $sessionId;

    /**
     * @var integer
     * @ORM\Column(type="string")
     */
    protected $action;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $message;

    /**
     * @var RouteEntity
     * @ORM\ManyToOne(targetEntity="Devrun\CmsModule\Entities\RouteEntity")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $route;

    /**
     * @var CategoryEntity
     * @ORM\ManyToOne(targetEntity="Devrun\CatalogModule\Entities\CategoryEntity")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $category;

    /**
     * @var ProductEntity
     * @ORM\ManyToOne(targetEntity="Devrun\CatalogModule\Entities\ProductEntity")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $product;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $extra;

    /**
     * @var string
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    protected $ip;



    /**
     * TrackingEntity constructor.
     *
     * @param int    $sessionId
     * @param int    $action
     * @param string $message
     */
    public function __construct($sessionId, $action, $message)
    {
        $this->sessionId = $sessionId;
        $this->action    = $action;
        $this->message   = $message;
    }

    /**
     * @return int
     */
    public function getAction(): int
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param int $action
     *
     * @return TrackingEntity
     */
    public function setAction(int $action): TrackingEntity
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @param string $message
     *
     * @return TrackingEntity
     */
    public function setMessage(string $message): TrackingEntity
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param RouteEntity $route
     *
     * @return TrackingEntity
     */
    public function setRoute(RouteEntity $route): TrackingEntity
    {
        $this->route = $route;
        return $this;
    }

    /**
     * @param CategoryEntity $category
     *
     * @return TrackingEntity
     */
    public function setCategory(CategoryEntity $category): TrackingEntity
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @param ProductEntity $product
     *
     * @return TrackingEntity
     */
    public function setProduct(ProductEntity $product): TrackingEntity
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @param string $extra
     *
     * @return TrackingEntity
     */
    public function setExtra(string $extra): TrackingEntity
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * @param string $ip
     *
     * @return TrackingEntity
     */
    public function setIp(string $ip): TrackingEntity
    {
        $this->ip = $ip;
        return $this;
    }




}