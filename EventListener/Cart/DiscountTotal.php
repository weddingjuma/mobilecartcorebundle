<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\CartComponent\Total;

/**
 * Class DiscountTotal
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class DiscountTotal extends Total
{
    const KEY = 'discounts';
    const LABEL = 'Discount';

    /**
     * @var array
     */
    protected $discounts;

    /**
     * @var \MobileCart\CoreBundle\Service\DiscountService
     */
    protected $discountService;

    /**
     * @param $discounts
     * @return $this
     */
    public function setDiscounts(array $discounts)
    {
        $this->discounts = $discounts;
        return $this;
    }

    /**
     * @return array
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }

    /**
     * @param $discountService
     * @return $this
     */
    public function setDiscountService($discountService)
    {
        $this->discountService = $discountService;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscountService()
    {
        return $this->discountService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCartTotalCollect(CoreEvent $event)
    {
        // this includes both pre-tax and post-tax discounts
        //  if needed, this class can be split into 2 Totals
        //  before and after the tax total
        //  call $cart->getCalculator():
        // getPreTaxDiscountTotal() and getPostTaxDiscountTotal()

        /** @var \MobileCart\CoreBundle\CartComponent\Cart $cart */
        $cart = $event->getCart();

        if ($cart->hasItems()
            && $event->getApplyAutoDiscounts()
        ) {

            $asComponent = true;
            $discounts = $this->getDiscountService()
                ->setCart($cart)
                ->getAutoDiscounts($asComponent);

            if ($discounts) {
                /** @var \MobileCart\CoreBundle\CartComponent\Discount $discount */
                foreach($discounts as $discount) {
                    if ($discount->reapplyIfValid($cart)
                        && $discount->hasPromoSkus()
                    ) {

                        // todo: find a better place for handling promo skus
                        //  this assumes the sku price is zero

                        foreach($discount->getPromoSkus() as $sku) {

                            if ($cart->hasSku($sku)) {
                                continue;
                            }

                            $product = $this->getDiscountService()->getEntityService()
                                ->findOneBy(EntityConstants::PRODUCT, [
                                    'sku' => $sku,
                                ]);

                            if ($product) {
                                $qty = 1;
                                $item = $cart->createItem();
                                $data = $product->getData();
                                $data['product_id'] = $data['id'];
                                unset($data['id']);
                                $item->fromArray($data);
                                $item->setPrice(0.00);
                                $item->setQty($qty);
                                $item->set('promo_qty', $qty); // use this to block changing qty later
                                $cart->addItem($item);

                            } else {
                                if (!$discount->hasItems() && !$discount->hasShipments()) {
                                    $cart->removeDiscount($discount);
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        $discountTotal = $cart->getCalculator()
            ->getDiscountTotal();

        $discounts = is_array($cart->getDiscounts())
            ? $cart->getDiscounts()
            : [];

        $this->setDiscounts($discounts);

        $event->setCart($cart);

        $this->setKey(self::KEY)
            ->setLabel(self::LABEL)
            ->setValue($discountTotal)
            ->setIsAdd(0); // subtract

        $event->addTotal($this);
    }
}
