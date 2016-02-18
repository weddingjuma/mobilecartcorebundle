<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

use MobileCart\CoreBundle\Constants\CheckoutConstants;

class CheckoutSubmitOrder
{
    protected $event;

    protected $checkoutSessionService;

    protected $orderService;

    protected $router;

    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function setCheckoutSessionService($checkoutSessionService)
    {
        $this->checkoutSessionService = $checkoutSessionService;
        return $this;
    }

    public function getCheckoutSessionService()
    {
        return $this->checkoutSessionService;
    }

    public function setOrderService($orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    public function getOrderService()
    {
        return $this->orderService;
    }

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function onCheckoutSubmitOrder(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $isValid = 0;
        $returnData['messages'] = []; // r[messages] = [msg, msg, msg]
        $returnData['invalid_sections'] = []; // r[invalid_sections] = [a, b, c]
        $returnData['invalid'] = []; // r[invalid][section][field] = [msg, msg, msg]
        $returnData['success'] = $isValid;

        // todo : keep a count of invalid requests, logout/lockout user if excessive

        if (!$this->getCheckoutSessionService()->getIsValidBillingAddress()) {
            $returnData['invalid_sections'][] = CheckoutConstants::STEP_BILLING_ADDRESS;
        }

        if (!$this->getCheckoutSessionService()->getIsValidShippingAddress()) {
            $returnData['invalid_sections'][] = CheckoutConstants::STEP_SHIPPING_ADDRESS;
        }

        if (!$this->getCheckoutSessionService()->getIsValidTotals()) {
            $returnData['invalid_sections'][] = CheckoutConstants::STEP_TOTALS_DISCOUNTS;
        }

        if (!$this->getCheckoutSessionService()->getIsValidPaymentMethod()) {
            $returnData['invalid_sections'][] = CheckoutConstants::STEP_PAYMENT_METHODS;
        }

        if ($returnData['invalid_sections']) {
            $returnData['success'] = 0;

            $response = new JsonResponse($returnData);

            $event->setReturnData($returnData)
                ->setResponse($response);

            return false; // return early, return value has no effect
        }

        $paymentMethodCode = $this->getCheckoutSessionService()
            ->getPaymentMethodCode();

        $paymentData = $this->getCheckoutSessionService()
            ->getPaymentData();

        $paymentMethodService = $this->getCheckoutSessionService()
            ->findPaymentMethodServiceByCode($paymentMethodCode);

        $cart = $this->getCheckoutSessionService()
            ->getCartSessionService()
            ->getCart();

        $orderService = $this->getOrderService()
            ->setCart($cart);

        // todo : create customer, if guest checkout

        if ($orderService->getEnableCreatePayment()) {

            $orderService->setPaymentMethodService($paymentMethodService)
                ->setPaymentData($paymentData);

        }

        // create order, orderItems, orderShipments, orderInvoice
        //  and payment, if necessary
        try {
            $orderService->submitCart();
            $isValid = 1;
        } catch(\Exception $e) {
            $returnData['messages'] = $e->getMessage(); // todo : build on this, handle all exceptions
        }

        $event->setIsValid($isValid);

        if ($isValid) {

            $event->setOrder($orderService->getOrder());

            $this->getCheckoutSessionService()
                ->getCartSessionService()
                ->getSession()
                ->set('order_id', $orderService->getOrder()->getId());

            $returnData['redirect_url'] = $this->getRouter()->generate('cart_checkout_success', []);
        }

        $returnData['success'] = $isValid;

        $response = new JsonResponse($returnData);

        $event->setReturnData($returnData)
            ->setResponse($response);

    }
}
