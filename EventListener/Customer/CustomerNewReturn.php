<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;

class CustomerNewReturn
{
    protected $request;

    protected $varSet;

    protected $entityService;

    protected $themeService;

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

    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    public function getThemeService()
    {
        return $this->themeService;
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

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setVarSet($varSet)
    {
        $this->varSet = $varSet;
        return $this;
    }

    public function getVarSet()
    {
        return $this->varSet;
    }

    public function onCustomerNewReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $entity = $event->getEntity();
        $varSet = $this->getVarSet();
        $objectType = $event->getObjectType();

        $typeSections = [];
        $returnData['template_sections'] = $typeSections;

        $form = $returnData['form'];
        $returnData['form'] = $form->createView();
        $returnData['entity'] = $entity;

        if ($messages = $event->getMessages()) {
            foreach($messages as $code => $message) {
                $event->getRequest()->getSession()->getFlashBag()->add($code, $message);
            }
        }

        $response = $this->getThemeService()
            ->render('admin', 'Customer:new.html.twig', $returnData);

        $event->setResponse($response);
        $event->setReturnData($returnData);
    }
}
