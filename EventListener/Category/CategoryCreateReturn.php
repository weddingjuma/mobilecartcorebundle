<?php

namespace MobileCart\CoreBundle\EventListener\Category;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CategoryCreateReturn
 * @package MobileCart\CoreBundle\EventListener\Category
 */
class CategoryCreateReturn
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
    public function onCategoryCreateReturn(CoreEvent $event)
    {
        $redirectUrl = $this->getRouter()->generate('cart_admin_category_edit', [
            'id' => $event->getEntity()->getId()
        ]);

        $event->flashMessages();

        if ($event->isJsonResponse()) {

            $event->setResponse(new JsonResponse([
                'success' => $event->getSuccess(),
                'redirect_url' => $redirectUrl,
                'messages' => $event->getMessages(),
            ]));

        } else {

            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }
}
