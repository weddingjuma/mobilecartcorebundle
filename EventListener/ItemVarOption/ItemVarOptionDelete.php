<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarOption;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;

class ItemVarOptionDelete
{

    protected $entityService;

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

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function onItemVarOptionDelete(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $entity = $event->getEntity();
        $this->getEntityService()->remove($entity, EntityConstants::ITEM_VAR);

        $event->setReturnData($returnData);
    }
}
