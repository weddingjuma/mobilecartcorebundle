<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

class CheckoutUpdateShippingMethod
{
    protected $event;

    protected $formFactory;

    protected $checkoutSessionService;

    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
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

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function onCheckoutUpdateShippingMethod(Event $event)
    {
        if (!$this->getCheckoutSessionService()->getCartSessionService()->getShippingService()->getIsShippingEnabled()) {
            return false;
        }

        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $request = $event->getRequest();
        $formType = $event->getForm();

        // dummy object
        $entity = new \stdClass();
        $entity->shipping_method = '';

        $form = $this->getFormFactory()->create($formType, $entity, [
            'action' => $event->getAction(),
            'method' => $event->getMethod(),
            'translation_domain' => 'checkout',
        ]);

        $requestData = $request->request->all();
        $form->submit($requestData);
        $isValid = $form->isValid();

        if ($isValid) {

            $methodCode = $request->get('shipping_method');

            $cartSession = $this->getCheckoutSessionService()
                ->getCartSessionService();

            $cart = $cartSession->getCart();

            if ($cart->hasShippingMethodCode($methodCode)) {

                // todo : double-check this is the best way
                $shipment = $cart->getShippingMethod($cart->findShippingMethodIdx('code', $methodCode));

                $cartSession
                    ->removeShipments()
                    ->addShipment($shipment)
                    ->collectTotals();

                $cart = $cartSession->getCart();

                $returnData['cart'] = $cart;
            }
        }

        $this->getCheckoutSessionService()->setIsValidShippingMethod($isValid);

        $returnData['success'] = $isValid;
        $returnData['messages'] = [];
        $returnData['invalid'] = [];

        $response = new JsonResponse($returnData);

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
