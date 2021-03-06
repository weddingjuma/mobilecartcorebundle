<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderUpdate
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderUpdate
{
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
     * @return \MobileCart\CoreBundle\Service\CartTotalService
     */
    public function getCartTotalService()
    {
        return $this->getCartService()->getCartTotalService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\DiscountService
     */
    public function getDiscountService()
    {
        return $this->getCartService()->getDiscountService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyServiceInterface
     */
    public function getCurrencyService()
    {
        return $this->getCartService()->getCurrencyService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @param CoreEvent $event
     */
    public function onOrderUpdate(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\Order $entity */
        $entity = $event->getEntity();
        $request = $event->getRequest();
        $customerId = $request->get('customer_id', 0);

        if ($entity->get('customer_id') != $customerId) {
            $customer = $this->getEntityService()->find(EntityConstants::CUSTOMER, $customerId);
            if ($customer) {
                $entity->setCustomer($customer);
            }
        }

        $this->getEntityService()->beginTransaction();

        try {
            $this->getEntityService()->persist($entity);
            if ($entity->getItemVarSet() && $event->getFormData()) {
                $this->getEntityService()->persistVariants($entity, $event->getFormData());
            }
            $event->setSuccess(true);
            $this->getEntityService()->commit();
            $event->addSuccessMessage('Order Updated !');
        } catch(\Exception $e) {
            $this->getEntityService()->rollBack();
            $event->addErrorMessage('An error occurred while saving the Order');
            return;
        }

        $username = $event->getUser()
            ? $event->getUser()->getEmail()
            : $entity->getEmail();

        /** @var \MobileCart\CoreBundle\Entity\OrderHistory $history */
        $history = $this->getEntityService()->getInstance(EntityConstants::ORDER_HISTORY);
        $history->setCreatedAt(new \DateTime('now'))
            ->setOrder($entity)
            ->setUser($username)
            ->setMessage('Order Updated')
            ->setHistoryType(\MobileCart\CoreBundle\Entity\OrderHistory::TYPE_STATUS);

        try {
            $this->getEntityService()->persist($history);
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while saving Order History');
        }

        // update tracking numbers on shipments, if necessary
        $request = $event->getRequest();
        $tracking = $request->get('tracking', []);

        $shipments = $entity->getShipments();
        if ($shipments && $tracking) {
            foreach($entity->getShipments() as $shipment) {
                if (isset($tracking[$shipment->getId()])
                    && $shipment->getTracking() != $tracking[$shipment->getId()]
                ) {
                    $shipment->setTracking($tracking[$shipment->getId()]);
                    try {
                        $this->getEntityService()->persist($shipment);
                    } catch(\Exception $e) {
                        $event->addErrorMessage('An error occurred while saving Tracking on a Shipment');
                    }
                }
            }
        }
    }
}
