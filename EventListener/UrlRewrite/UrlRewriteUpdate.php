<?php

namespace MobileCart\CoreBundle\EventListener\UrlRewrite;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class UrlRewriteUpdate
 * @package MobileCart\CoreBundle\EventListener\UrlRewrite
 */
class UrlRewriteUpdate
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
    public function onUrlRewriteUpdate(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $entity = $event->getEntity();
        $this->getEntityService()->persist($entity);

        if ($entity && $event->getRequest()->getSession()) {
            $event->getRequest()->getSession()->getFlashBag()->add(
                'success',
                'URL Rewrite Updated!'
            );
        }

        $event->setReturnData($returnData);
    }
}
