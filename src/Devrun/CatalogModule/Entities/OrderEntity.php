<?php
/**
 * This file is part of nivea-2019-klub-rewards.
 * Copyright (c) 2019
 *
 * @file    OrderEntity.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Entities;

use Devrun\CmsModule\Entities\BlameableTrait;
use Devrun\CmsModule\Entities\UserEntity;
use Devrun\DoctrineModule\Entities\DateTimeTrait;
use Devrun\DoctrineModule\Entities\IdentifiedEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * Class OrderEntity
 * @ORM\Cache(region="order")
 * @ORM\Entity(repositoryClass="Devrun\CatalogModule\Repositories\OrderRepository")
 * @ORM\Table(name="catalog_order",
 * indexes={
 *  @ORM\Index(name="order_session_idx", columns={"session_id"}),
 * })
 *
 * @package Devrun\CatalogModule\Entities
 */
class OrderEntity
{

    use IdentifiedEntityTrait;
    use MagicAccessors;
    use DateTimeTrait;
    use BlameableTrait;


    /**
     * @var integer
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    protected $sessionId;

    /**
     * @var UserEntity
     * @ORM\ManyToOne(targetEntity="Devrun\CmsModule\Entities\UserEntity")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var ProductEntity
     * @ORM\ManyToOne(targetEntity="ProductEntity")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $product;

    /**
     * @var string
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    protected $price = 0;

    /**
     * @var integer
     * @ORM\Column(type="integer", options={"default": 1})
     */
    protected $amount = 1;

    /**
     * OrderEntity constructor.
     *
     * @param ProductEntity $productEntity
     */
    public function __construct(ProductEntity $productEntity, UserEntity $userEntity)
    {
        $this->product = $productEntity;
        $this->user    = $userEntity;
    }


    /**
     * @param UserEntity $user
     *
     * @return OrderEntity
     */
    public function setUser(UserEntity $user): OrderEntity
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param string $price
     *
     * @return OrderEntity
     */
    public function setPrice(string $price): OrderEntity
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @param int $amount
     *
     * @return OrderEntity
     */
    public function setAmount(int $amount): OrderEntity
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return ProductEntity
     */
    public function getProduct(): ProductEntity
    {
        return $this->product;
    }

    /**
     * @return UserEntity
     */
    public function getUser()
    {
        return $this->user;
    }




}