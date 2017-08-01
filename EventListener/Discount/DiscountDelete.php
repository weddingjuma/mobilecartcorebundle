<?php

namespace MobileCart\CoreBundle\EventListener\Discount;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class DiscountDelete
 * @package MobileCart\CoreBundle\EventListener\Discount
 */
class DiscountDelete
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @param $entityService
     * @return $this
     */
    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onDiscountDelete(CoreEvent $event)
    {
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();
        $this->getEntityService()->remove($entity, EntityConstants::DISCOUNT);

        if ($entity && $event->getRequest()->getSession()) {
            $event->getRequest()->getSession()->getFlashBag()->add(
                'success',
                'Discount Deleted!'
            );
        }

        $event->setReturnData($returnData);
    }
}
