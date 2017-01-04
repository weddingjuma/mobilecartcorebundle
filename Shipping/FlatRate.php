<?php

namespace MobileCart\CoreBundle\Shipping;

use MobileCart\CoreBundle\Shipping\Rate;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class FlatRate
 * @package MobileCart\CoreBundle\EventListener\Shipping
 *
 * This is a basic Shipping Rate
 *  the price is set in the service configuration via the magic setter in Rate
 *  and can be changed in the admin; along with cart pre-conditions
 */
class FlatRate extends Rate
{
    protected $event;

    protected $isEnabled = 1;

    public function __construct()
    {
        parent::__construct();
    }

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    public function setIsEnabled($yesNo = 1)
    {
        $this->isEnabled = $yesNo;
        return $this;
    }

    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    protected function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    /**
     * Get rates while filtering on criteria
     *
     * @param Event $event
     */
    public function onShippingRateCollect(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        if ($this->getIsEnabled()) {
            $event->addRate($this);
        }

        $event->setReturnData($returnData);
    }
}
