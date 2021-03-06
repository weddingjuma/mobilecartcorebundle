<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Intl\Intl;

/**
 * Class CustomerType
 * @package MobileCart\CoreBundle\Form
 */
class CustomerType extends AbstractType
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @param \MobileCart\CoreBundle\Service\CartService $cartService
     * @return $this
     */
    public function setCartService(\MobileCart\CoreBundle\Service\CartService $cartService)
    {
        $this->cartService = $cartService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->cartService;
    }

    /**
     * @return array
     */
    public function getCountries()
    {
        $allCountries = Intl::getRegionBundle()->getCountryNames();
        $allowedCountries = $this->getCartService()->getAllowedCountryIds();

        $countries = [];
        foreach($allowedCountries as $countryId) {
            $countries[$countryId] = $allCountries[$countryId];
        }

        return $countries;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('is_enabled', CheckboxType::class, [
                'required' => false
            ])
            ->add('email', TextType::class,[
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_firstname', TextType::class, [
                'label' => 'First Name',
                'attr' => ['class' => 'billing-input'],
            ])
            ->add('billing_lastname', TextType::class, [
                'label' => 'Last Name',
                'attr' => ['class' => 'billing-input'],
            ])
            ->add('billing_company', TextType::class, [
                'label' => 'Company',
                'attr' => ['class' => 'billing-input'],
            ])
            ->add('billing_phone', TextType::class, [
                'label' => 'Billing Phone',
                'attr' => ['class' => 'billing-input'],
            ])
            ->add('billing_street', TextType::class, [
                'label' => 'Billing Street',
                'attr' => ['class' => 'billing-input'],
            ])
            ->add('billing_street2', TextType::class, [
                'label' => 'Billing Street 2',
                'attr' => ['class' => 'billing-input'],
            ])
            ->add('billing_city', TextType::class, [
                'label' => 'Billing City',
                'attr' => ['class' => 'billing-input'],
            ])
            ->add('billing_region', TextType::class, [
                'label' => 'Billing State',
                'attr' => [
                    'class' => 'region-input billing-input',
                ],
            ])
            ->add('billing_postcode', TextType::class, [
                'label' => 'Billing Postal Code',
                'attr' => ['class' => 'billing-input'],
            ])
            ->add('billing_country_id', ChoiceType::class, [
                'label' => 'Billing Country',
                'choices' => array_flip($this->getCountries()),
                'attr' => [
                    'class' => 'country-input billing-input',
                ],
                'choices_as_values' => true,
            ])
            ->add('is_shipping_same', CheckboxType::class, [
                'label' => 'Is Shipping Same as Billing ?'
            ])
            ->add('shipping_firstname', TextType::class, [
                'label' => 'Shipping First Name',
                'required' => false,
                'attr' => ['class' => 'shipping-input'],
            ])
            ->add('shipping_lastname', TextType::class, [
                'label' => 'Shipping Last Name',
                'required' => false,
                'attr' => ['class' => 'shipping-input'],
            ])
            ->add('shipping_company', TextType::class, [
                'label' => 'Company',
                'required' => false,
                'attr' => ['class' => 'shipping-input'],
            ])
            ->add('shipping_phone', TextType::class, [
                'label' => 'Shipping Phone',
                'required' => false,
                'attr' => ['class' => 'shipping-input'],
            ])
            ->add('shipping_street', TextType::class, [
                'label' => 'Shipping Street',
                'required' => false,
                'attr' => ['class' => 'shipping-input'],
            ])
            ->add('shipping_street2', TextType::class, [
                'label' => 'Shipping Street 2',
                'required' => false,
                'attr' => ['class' => 'shipping-input'],
            ])
            ->add('shipping_city', TextType::class, [
                'label' => 'Shipping City',
                'required' => false,
                'attr' => ['class' => 'shipping-input'],
            ])
            ->add('shipping_region', TextType::class, [
                'label' => 'Shipping State',
                'required' => false,
                'attr' => [
                    'class' => 'region-input shipping-input'
                ],
            ])
            ->add('shipping_postcode', TextType::class, [
                'label' => 'Shipping Postal Code',
                'required' => false,
                'attr' => ['class' => 'shipping-input'],
            ])
            ->add('shipping_country_id', ChoiceType::class, [
                'label' => 'Shipping Country',
                'choices' => array_flip($this->getCountries()),
                'required' => false,
                'attr' => [
                    'class' => 'country-input shipping-input',
                ],
                'choices_as_values' => true,
            ])
            ->add('is_locked', CheckboxType::class, [
                'required' => false,
            ])
            ->add('is_password_expired', CheckboxType::class, [
                'required' => false,
            ])
            ->add('is_expired', CheckboxType::class, [
                'required' => false,
            ])
            ->add('api_key', TextType::class, [
                'required' => false,
            ]);

        // todo : ensure the validation messages for this make it into flash messages eg "Cannot be blank"
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $customer = $event->getData();
            $form = $event->getForm();

            // check if the Customer object is "new"
            // If no data is passed to the form, the data is "null".
            // This should be considered a new "Customer"
            if (!$customer || !$customer->getId()) {

                $form->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match.',
                    'options' => ['attr' => ['class' => 'password-field']],
                    'required' => true,
                    'first_options'  => ['label' => 'Password'],
                    'second_options' => ['label' => 'Repeat Password'],
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]);

            } else {

                $form->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match.',
                    'options' => ['attr' => ['class' => 'password-field']],
                    'required' => false,
                    'first_options'  => ['label' => 'Password'],
                    'second_options' => ['label' => 'Repeat Password'],
                    'mapped' => false,
                ]);
            }
        });
    }

    public function getBlockPrefix()
    {
        return 'customer';
    }
}
