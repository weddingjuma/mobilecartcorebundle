<?php

namespace MobileCart\CoreBundle\EventListener\Content;

use Symfony\Component\EventDispatcher\Event;

class ContentViewReturn
{
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

    public function onContentViewReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $response = $this->getThemeService()
            ->render('frontend', 'Content:view.html.twig', $returnData);

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }
}
