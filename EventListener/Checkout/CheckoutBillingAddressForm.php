<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\CheckoutConstants;

/**
 * Class CheckoutBillingAddressForm
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutBillingAddressForm
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var string
     */
    protected $formTypeClass = '';

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var \MobileCart\CoreBundle\Service\OrderService
     */
    protected $orderService;

    /**
     * @param $orderService
     * @return $this
     */
    public function setOrderService($orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\OrderService
     */
    public function getOrderService()
    {
        return $this->orderService;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->getOrderService()->getCartService();
    }

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @return $this
     */
    public function setFormFactory(\Symfony\Component\Form\FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    /**
     * @return \Symfony\Component\Form\FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @param string $formTypeClass
     * @return $this
     */
    public function setFormTypeClass($formTypeClass)
    {
        $this->formTypeClass = $formTypeClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormTypeClass()
    {
        return $this->formTypeClass;
    }

    /**
     * @param \MobileCart\CoreBundle\Service\ThemeService $themeService
     * @return $this
     */
    public function setThemeService(\MobileCart\CoreBundle\Service\ThemeService $themeService)
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
     * @return mixed
     */
    public function getCustomerId()
    {
        return (int) $this->getCheckoutSessionService()->getCartService()->getCustomerId();
    }

    /**
     * @return bool
     */
    public function getDisplayEmailInput()
    {
        return $this->getCartService()->getCheckoutFormService()->getAllowGuestCheckout() && !$this->getCustomerId();
    }

    /**
     * @param CoreEvent $event
     */
    public function onCheckoutForm(CoreEvent $event)
    {
        if ($event->get('step_number', 0) > 0) {
            $event->set('step_number', $event->get('step_number') + 1);
        } else {
            $event->set('step_number', 1);
        }

        $form = $this->getFormFactory()->create($this->getFormTypeClass(), [], [
            'method' => 'post',
            'action' => $this->getRouter()->generate('cart_checkout_update_section', [
                'section' => CheckoutConstants::STEP_BILLING_ADDRESS
            ]),
        ]);

        $billingFields = $this->getDisplayEmailInput()
            ? ['email']
            : [];

        $billingFields = array_merge(
            $billingFields,
            [
                'billing_firstname',
                'billing_lastname',
                'billing_company',
                'billing_street',
                'billing_street2',
                'billing_city',
                'billing_region',
                'billing_postcode',
                'billing_country_id',
                'billing_phone',
                'is_shipping_same',
            ]
        );

        $javascripts = $event->getReturnData('javascripts', []);

        $tplPath = $this->getThemeService()->getTemplatePath($this->getThemeService()->getThemeConfig()->getFrontendTheme());

        $cartService = $this->getCartService();

        $cart = $cartService->getCart(); // cart entity should already be set on cart service
        $customer = $cart->getCustomer();

        foreach($billingFields as $field) {

            $customerValue = $customer->get($field);

            switch($field) {
                case 'is_shipping_same':
                    // must be a new "feature" in Symfony? It won't take a '1' anymore?
                    $form->get($field)->setData((bool) $customerValue);
                    break;
                default:
                    if (!is_null($customerValue)) {
                        $form->get($field)->setData($customerValue);
                    }
                    break;
            }
        }

        $sectionData = [
            'section' => CheckoutConstants::STEP_BILLING_ADDRESS,
            'step_number' => $event->get('step_number'),
            'label' => 'Billing Address',
            'fields' => $billingFields,
            'post_url' => $this->getRouter()->generate('cart_checkout_update_section', [
                'section' => CheckoutConstants::STEP_BILLING_ADDRESS
            ]),
            'template' => $tplPath . 'Checkout:address_billing.html.twig',
            'country_regions' => $this->getCartService()->getCountryRegions(),
        ];

        if ($event->get('single_step', '')) {
            // if there's a single step, we only want the form for this step
            if ($event->get('single_step', '') == CheckoutConstants::STEP_BILLING_ADDRESS) {
                $event->setForm($form);
                $event->set('section_data', $sectionData);

                // if it's not a submission, then we render the template
                if ($event->getRequest()->getMethod() !== 'POST') {

                    $template = $event->get('template', '')
                        ? $event->get('template', '')
                        : 'Checkout:section_full.html.twig';

                    // add js for handling a single step on each page
                    $javascripts[] = [
                        'js_template' => $tplPath . 'Checkout:section_full_js.html.twig',
                    ];

                    $sectionData['javascripts'] = $javascripts;
                    $sectionData['form_view'] = $form->createView();

                    $event->setResponse($this->getThemeService()->renderFrontend(
                        $template,
                        $sectionData
                    ));
                }
            }
        } else {
            // if it's not a single step, then we want the forms for all steps
            $sectionData['form_view'] = $form->createView();

            if (!$event->getRequest()
                || $event->getRequest()->getMethod() !== 'POST'
            ) {

                $javascripts[] = [
                    'js_template' => $tplPath . 'Checkout:section_address_js.html.twig',
                    'data' => $sectionData,
                ];

                $event->setReturnData('javascripts', $javascripts);
                $event->setReturnData('country_regions', $sectionData['country_regions']);
            }

            $sections = $event->getReturnData('sections', []);
            $sections[CheckoutConstants::STEP_BILLING_ADDRESS] = $sectionData;
            $event->setReturnData('sections', $sections);
        }
    }
}
