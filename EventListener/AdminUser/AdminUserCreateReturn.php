<?php

namespace MobileCart\CoreBundle\EventListener\AdminUser;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class AdminUserCreateReturn
 * @package MobileCart\CoreBundle\EventListener\AdminUser
 */
class AdminUserCreateReturn
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
    public function onAdminUserCreateReturn(CoreEvent $event)
    {
        $url = $this->getRouter()->generate('cart_admin_admin_user_edit', [
            'id' => $event->getEntity()->getId()
        ]);

        $event->flashMessages();

        if ($event->isJsonResponse()) {

            $event->setResponse(new JsonResponse([
                'success' => $event->getSuccess(),
                'entity' => $event->getEntity()->getData(),
                'redirect_url' => $url,
                'messages' => $event->getMessages(),
            ]));

        } else {

            $event->setResponse(new RedirectResponse($url));
        }
    }
}
