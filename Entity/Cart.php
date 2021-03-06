<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cart
 *
 * @ORM\Table(name="cart")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\CartRepository")
 */
class Cart
    extends AbstractCartEntity
    implements CartEntityInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $created_at;

    /**
     * @var string $hash_key
     *
     * @ORM\Column(name="hash_key", type="string", length=255, nullable=true)
     */
    protected $hash_key;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Customer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * })
     */
    protected $customer;

    /**
     * @var \MobileCart\CoreBundle\Entity\CartItem
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CartItem", mappedBy="cart")
     */
    protected $cart_items;

    /**
     * @var string $currency
     *
     * @ORM\Column(name="currency", type="string", length=8, nullable=true)
     */
    protected $currency;

    /**
     * @var float $total
     *
     * @ORM\Column(name="total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $total;

    /**
     * @var float $item_total
     *
     * @ORM\Column(name="item_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $item_total;

    /**
     * @var float $tax_total
     *
     * @ORM\Column(name="tax_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $tax_total;

    /**
     * @var float $discount_total
     *
     * @ORM\Column(name="discount_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $discount_total;

    /**
     * @var float $shipping_total
     *
     * @ORM\Column(name="shipping_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $shipping_total;

    /**
     * @var string $base_currency
     *
     * @ORM\Column(name="base_currency", type="string", length=8, nullable=true)
     */
    protected $base_currency;

    /**
     * @var float $base_total
     *
     * @ORM\Column(name="base_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $base_total;

    /**
     * @var float $base_item_total
     *
     * @ORM\Column(name="base_item_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $base_item_total;

    /**
     * @var float $base_tax_total
     *
     * @ORM\Column(name="base_tax_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $base_tax_total;

    /**
     * @var float $base_discount_total
     *
     * @ORM\Column(name="base_discount_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $base_discount_total;

    /**
     * @var float $base_shipping_total
     *
     * @ORM\Column(name="base_shipping_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $base_shipping_total;

    /**
     * @var string $json
     *
     * @ORM\Column(name="json", type="text", nullable=true)
     */
    protected $json;

    /**
     * @var string $payment_method_code
     *
     * @ORM\Column(name="payment_method_code", type="string", length=32, nullable=true)
     */
    protected $payment_method_code;

    /**
     * @var string $payment_authorization
     *
     * @ORM\Column(name="payment_authorization", type="string", length=255, nullable=true)
     */
    protected $payment_authorization;

    /**
     * @var string $payment_info
     *
     * @ORM\Column(name="payment_info", type="text", nullable=true)
     */
    protected $payment_info;

    /**
     * @var string $checkout_state
     *
     * @ORM\Column(name="checkout_state", type="text", nullable=true)
     */
    protected $checkout_state;

    /**
     * @var boolean $is_wishlist
     *
     * @ORM\Column(name="is_wishlist", type="boolean", nullable=true)
     */
    protected $is_wishlist;

    /**
     * @var boolean $is_converted
     *
     * @ORM\Column(name="is_converted", type="boolean", nullable=true)
     */
    protected $is_converted;

    public function __construct()
    {
        $this->cart_items = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return string
     */
    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::CART;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'created_at' => $this->getCreatedAt(),
            'hash_key' => $this->getHashKey(),
            'currency' => $this->getCurrency(),
            'total' => $this->getTotal(),
            'item_total' => $this->getItemTotal(),
            'tax_total' => $this->getTaxTotal(),
            'discount_total' => $this->getDiscountTotal(),
            'shipping_total' => $this->getShippingTotal(),
            'base_currency' => $this->getBaseCurrency(),
            'base_total' => $this->getBaseTotal(),
            'base_item_total' => $this->getBaseItemTotal(),
            'base_tax_total' => $this->getBaseTaxTotal(),
            'base_discount_total' => $this->getBaseDiscountTotal(),
            'base_shipping_total' => $this->getBaseShippingTotal(),
            'is_wishlist' => $this->getIsWishlist(),
            'is_converted' => $this->getIsConverted(),
        ];
    }

    /**
     * Set json
     *
     * @param string $json
     * @return $this
     */
    public function setJson($json)
    {
        $this->json = $json;
        return $this;
    }

    /**
     * Get json
     *
     * @return string
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * Set checkout_state
     *
     * @param string $checkoutState
     * @return $this
     */
    public function setCheckoutState($checkoutState)
    {
        $this->checkout_state = $checkoutState;
        return $this;
    }

    /**
     * Get checkout_state
     *
     * @return string
     */
    public function getCheckoutState()
    {
        return $this->checkout_state;
    }

    /**
     * @param string $paymentMethodCode
     * @return $this
     */
    public function setPaymentMethodCode($paymentMethodCode)
    {
        $this->payment_method_code = $paymentMethodCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMethodCode()
    {
        return $this->payment_method_code;
    }

    /**
     * @param string $paymentInfo
     * @return $this
     */
    public function setPaymentInfo($paymentInfo)
    {
        $this->payment_info = $paymentInfo;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentInfo()
    {
        return $this->payment_info;
    }

    /**
     * @param $paymentAuthorization
     * @return $this
     */
    public function setPaymentAuthorization($paymentAuthorization)
    {
        $this->payment_authorization = $paymentAuthorization;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentAuthorization()
    {
        return $this->payment_authorization;
    }

    /**
     * Set is_wishlist
     *
     * @param boolean $isWishlist
     * @return $this
     */
    public function setIsWishlist($isWishlist)
    {
        $this->is_wishlist = $isWishlist;
        return $this;
    }

    /**
     * Get is_wishlist
     *
     * @return boolean 
     */
    public function getIsWishlist()
    {
        return $this->is_wishlist;
    }

    /**
     * @param bool $isConverted
     * @return $this
     */
    public function setIsConverted($isConverted)
    {
        $this->is_converted = (bool) $isConverted;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsConverted()
    {
        return (bool) $this->is_converted;
    }

    /**
     * Set customer
     *
     * @param Customer $customer
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Get customer
     *
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param CartItem $cartItem
     * @return $this
     */
    public function addCartItem(CartItem $cartItem)
    {
        $this->cart_items[] = $cartItem;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|CartItem[]
     */
    public function getCartItems()
    {
        return $this->cart_items;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param string $hashKey
     * @return $this
     */
    public function setHashKey($hashKey)
    {
        $this->hash_key = $hashKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getHashKey()
    {
        return $this->hash_key;
    }

    /**
     * @param $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param $total
     * @return $this
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * Get total
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param $itemTotal
     * @return $this
     */
    public function setItemTotal($itemTotal)
    {
        $this->item_total = $itemTotal;
        return $this;
    }

    /**
     * Get item_total
     *
     * @return float
     */
    public function getItemTotal()
    {
        return $this->item_total;
    }

    /**
     * @param $taxTotal
     * @return $this
     */
    public function setTaxTotal($taxTotal)
    {
        $this->tax_total = $taxTotal;
        return $this;
    }

    /**
     * Get tax_total
     *
     * @return float
     */
    public function getTaxTotal()
    {
        return $this->tax_total;
    }

    /**
     * @param $discountTotal
     * @return $this
     */
    public function setDiscountTotal($discountTotal)
    {
        $this->discount_total = $discountTotal;
        return $this;
    }

    /**
     * Get discount_total
     *
     * @return float
     */
    public function getDiscountTotal()
    {
        return $this->discount_total;
    }

    /**
     * @param $shippingTotal
     * @return $this
     */
    public function setShippingTotal($shippingTotal)
    {
        $this->shipping_total = $shippingTotal;
        return $this;
    }

    /**
     * Get shipping_total
     *
     * @return float
     */
    public function getShippingTotal()
    {
        return $this->shipping_total;
    }

    /**
     * @param $currency
     * @return $this
     */
    public function setBaseCurrency($currency)
    {
        $this->base_currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseCurrency()
    {
        return $this->base_currency;
    }

    /**
     * @param $total
     * @return $this
     */
    public function setBaseTotal($total)
    {
        $this->base_total = $total;
        return $this;
    }

    /**
     * Get total
     *
     * @return float
     */
    public function getBaseTotal()
    {
        return $this->base_total;
    }

    /**
     * @param $itemTotal
     * @return $this
     */
    public function setBaseItemTotal($itemTotal)
    {
        $this->base_item_total = $itemTotal;
        return $this;
    }

    /**
     * Get item_total
     *
     * @return float
     */
    public function getBaseItemTotal()
    {
        return $this->base_item_total;
    }

    /**
     * @param $taxTotal
     * @return $this
     */
    public function setBaseTaxTotal($taxTotal)
    {
        $this->base_tax_total = $taxTotal;
        return $this;
    }

    /**
     * Get tax_total
     *
     * @return float
     */
    public function getBaseTaxTotal()
    {
        return $this->base_tax_total;
    }

    /**
     * @param $discountTotal
     * @return $this
     */
    public function setBaseDiscountTotal($discountTotal)
    {
        $this->base_discount_total = $discountTotal;
        return $this;
    }

    /**
     * Get discount_total
     *
     * @return float
     */
    public function getBaseDiscountTotal()
    {
        return $this->base_discount_total;
    }

    /**
     * @param $shippingTotal
     * @return $this
     */
    public function setBaseShippingTotal($shippingTotal)
    {
        $this->base_shipping_total = $shippingTotal;
        return $this;
    }

    /**
     * Get shipping_total
     *
     * @return float
     */
    public function getBaseShippingTotal()
    {
        return $this->base_shipping_total;
    }
}
