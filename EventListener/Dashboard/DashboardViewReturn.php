<?php

namespace MobileCart\CoreBundle\EventListener\Dashboard;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class DashboardViewReturn
 * @package MobileCart\CoreBundle\EventListener\Dashboard
 */
class DashboardViewReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

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
     * @param $themeService
     * @return $this
     */
    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ThemeService
     */
    public function getThemeService()
    {
        return $this->themeService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onDashboardViewReturn(CoreEvent $event)
    {
        $returnData = $event->getReturnData();

        if (!isset($returnData['template_sections'])) {
            $returnData['template_sections'] = [];
        }

        $response = $this->getThemeService()
            ->render('admin', 'Dashboard:index.html.twig', $returnData);

        $event->setResponse($response);
    }
}
