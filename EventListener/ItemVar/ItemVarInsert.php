<?php

namespace MobileCart\CoreBundle\EventListener\ItemVar;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ItemVarInsert
 * @package MobileCart\CoreBundle\EventListener\ItemVar
 */
class ItemVarInsert
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
    public function onItemVarInsert(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $entity = $event->getEntity();
        $entity->setUrlToken($this->getEntityService()->slugify($entity->getUrlToken()));
        $entity->setCode($this->getEntityService()->slugify($entity->getCode()));
        $this->getEntityService()->persist($entity);

        if ($entity && $event->getRequest()->getSession()) {
            $event->getRequest()->getSession()->getFlashBag()->add(
                'success',
                'Custom Field Created!'
            );
        }

        $event->setReturnData($returnData);
    }
}
