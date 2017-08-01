<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Form\CustomerForgotPasswordType;

/**
 * Class CustomerForgotPasswordForm
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerForgotPasswordForm
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    protected $formFactory;

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

    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCustomerForgotPasswordForm(CoreEvent $event)
    {
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();

        $formType = new CustomerForgotPasswordType();
        $form = $this->getFormFactory()->create($formType, $entity, [
            'action' => $event->getAction(),
            'method' => $event->getMethod(),
        ]);

        $formSections = [
            'general' => [
                'label' => 'General',
                'id' => 'general',
                'fields' => [
                    'email',
                ],
            ],
        ];

        $returnData['form_sections'] = $formSections;
        $returnData['form'] = $form;
        $event->setForm($form);
        $event->setReturnData($returnData);
    }
}
