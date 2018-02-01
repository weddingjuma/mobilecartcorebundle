<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\CheckoutConstants;

/**
 * Class CheckoutUpdateTotalsDiscounts
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutUpdateTotalsDiscounts
{
    /**
     * @var \MobileCart\CoreBundle\Service\OrderService
     */
    protected $orderService;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @param $orderService
     * @return $this
     */
    public function setOrderService($orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\OrderService
     */
    public function getOrderService()
    {
        return $this->orderService;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->getOrderService()->getCartService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
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
    public function onCheckoutUpdateTotalsDiscounts(CoreEvent $event)
    {
        $isValid = true;
        $invalid = [];

        $sectionData = $event->get('section_data', []);

        $nextSection = isset($sectionData['next_section'])
            ? $sectionData['next_section']
            : '';

        $this->getCheckoutSessionService()->setSectionIsValid(CheckoutConstants::STEP_TOTALS_DISCOUNTS, $isValid);

        $event->setReturnData('success', $isValid);
        $event->setReturnData('messages', $event->getMessages());
        $event->setReturnData('invalid', $invalid);

        $cartService = $this->getCheckoutSessionService()->getCartService();
        if ($isValid && !$cartService->getIsSpaEnabled()) {

            $event->setReturnData('redirect_url', $this->getRouter()->generate(
                'cart_checkout_section', [
                    'section' => $nextSection
                ]
            ));
        }

        $this->getCheckoutSessionService()->setIsValidTotals($isValid);
        $this->getCheckoutSessionService()->setSectionIsValid(CheckoutConstants::STEP_TOTALS_DISCOUNTS, $isValid);

        $event->setResponse(new JsonResponse($event->getReturnData()));
    }
}
