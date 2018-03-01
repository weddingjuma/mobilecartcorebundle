<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarSet;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ItemVarSetCreateReturn
 * @package MobileCart\CoreBundle\EventListener\ItemVarSet
 */
class ItemVarSetCreateReturn
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @return $this
     */
    public function setRouter(\Symfony\Component\Routing\RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param CoreEvent $event
     */
    public function onItemVarSetCreateReturn(CoreEvent $event)
    {
        $url = $this->getRouter()->generate('cart_admin_item_var_set_edit', [
            'id' => $event->getEntity()->getId()
        ]);

        $event->flashMessages();

        switch($event->getRequestAccept()) {
            case CoreEvent::JSON:

                $event->setResponse(new JsonResponse([
                    'success' => $event->getSuccess(),
                    'entity' => $event->getEntity()->getData(),
                    'redirect_url' => $url,
                    'messages' => $event->getMessages(),
                ]));

                break;
            default:

                $event->setResponse(new RedirectResponse($url));

                break;
        }
    }
}
