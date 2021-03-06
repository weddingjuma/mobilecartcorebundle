<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class UpdateTotalsShipping
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class UpdateTotalsShipping
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @param \MobileCart\CoreBundle\Service\CartService $cartService
     * @return $this
     */
    public function setCartService(\MobileCart\CoreBundle\Service\CartService $cartService)
    {
        $this->cartService = $cartService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->cartService;
    }

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @return $this
     */
    public function setRouter(\Symfony\Component\Routing\RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param CoreEvent $event
     */
    public function onUpdateTotalsShipping(CoreEvent $event)
    {
        if ($event->getSuccess()) {

            if ($this->getCartService()->hasItems()) {
                $this->getCartService()->collectAddressShipments($event->getRecollectShipping());
            }

            $this->getCartService()->saveCart();
            $event->setReturnData(CoreEvent::CART, $this->getCartService()->getCart());

            if ($event->isJsonResponse()) {

                $event->setResponse(new JsonResponse($event->getReturnData()));

            } else {

                $event->flashMessages();
                $event->setResponse(new RedirectResponse($this->getRouter()->generate('cart_view', [])));
            }
        } else {

            if ($event->isJsonResponse()) {

                $event->setResponse(new JsonResponse([
                    CoreEvent::SUCCESS => false,
                    CoreEvent::MESSAGES => $event->getMessages()
                ], $event->getResponseCode()));

            } else {

                $event->flashMessages();
                $event->setResponse(new RedirectResponse($this->getRouter()->generate('cart_view', [])));
            }
        }
    }
}
