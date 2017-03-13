<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use Symfony\Component\EventDispatcher\Event;

class OrderUpdateItems
{

    protected $cartSession;

    protected $cartTotalService;

    protected $discountService;

    protected $event;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    protected function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setCartSession($cartSession)
    {
        $this->cartSession = $cartSession;
        return $this;
    }

    public function getCartSession()
    {
        return $this->cartSession;
    }

    public function setCartTotalService($cartTotalService)
    {
        $this->cartTotalService = $cartTotalService;
        return $this;
    }

    public function getCartTotalService()
    {
        return $this->cartTotalService;
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

    public function onOrderUpdateItems(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $request = $event->getRequest();

        // set shipment method on cart
        $cartJson = $request->get('cart', '{}');
        $qtys = $request->get('qtys', []);

        if ($qtys) {
            foreach($qtys as $productId => $qty) {
                $this->getCartSession()->setProductQty($productId, $qty);
            }
        }

        $totals = $this->getCartSession()
            ->collectTotals()
            ->getTotals();

        $cart = $this->getCartSession()->getCart();
        $returnData['cart'] = $cart;

        $excludeDiscountIds = [];

        // todo: implement getCartDiscounts()

        $discounts = $this->getDiscountService()
            ->setCart($cart)
            ->getAutoDiscounts(true);

        $returnData['discounts'] = $discounts;

        $event->setReturnData($returnData);
    }
}