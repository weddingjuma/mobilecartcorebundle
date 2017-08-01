<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarOption;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ItemVarOptionDelete
 * @package MobileCart\CoreBundle\EventListener\ItemVarOption
 */
class ItemVarOptionDelete
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
    public function onItemVarOptionDelete(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $entity = $event->getEntity();
        $this->getEntityService()->remove($entity, EntityConstants::ITEM_VAR);

        if ($entity && $event->getRequest()->getSession()) {

            $event->getRequest()->getSession()->getFlashBag()->add(
                'success',
                'Custom Field Option Deleted!'
            );
        }

        $event->setReturnData($returnData);
    }
}
