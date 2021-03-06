<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use MobileCart\CoreBundle\CartComponent\Discount;

/**
 * Class DiscountType
 * @package MobileCart\CoreBundle\Form
 */
class DiscountType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('priority', IntegerType::class)
            ->add('value', NumberType::class)
            ->add('applied_as', ChoiceType::class, [
                'choices' => array_flip([
                    Discount::APPLIED_AS_FLAT => 'Flat',
                    Discount::APPLIED_AS_PERCENT => 'Percentage',
                ]),
                'attr' => [
                    'class' => 'discount-condition',
                ],
                'choices_as_values' => true,
            ])
            ->add('applied_to', ChoiceType::class, [
                'choices' => array_flip([
                    Discount::APPLIED_TO_SPECIFIED => 'Specified',
                    Discount::APPLIED_TO_ITEMS => 'All Products',
                    Discount::APPLIED_TO_SHIPMENTS => 'All Shipments',
                ]),
                'attr' => [
                    'class' => 'discount-condition',
                ],
                'choices_as_values' => true,
            ])
            ->add('is_compound', CheckboxType::class, [
                'required' => false,
                ])
            ->add('is_pre_tax', CheckboxType::class, [
                'required' => false,
                ])
            ->add('is_auto', CheckboxType::class, [
                'required' => false,
                ])
            ->add('is_stopper', CheckboxType::class, [
                'required' => false,
                ])
            ->add('is_max_per_item', CheckboxType::class, [
                'required' => false,
                ])
            ->add('is_proportional', CheckboxType::class, [
                'required' => false,
                ])
            ->add('start_time', TextType::class, [
                'required' => false,
                ])
            ->add('end_time', TextType::class, [
                'required' => false,
                ])
            ->add('max_amount', NumberType::class, [
                'required' => false,
                ])
            ->add('max_qty', NumberType::class, [
                'required' => false,
                ])
            ->add('coupon_code', TextType::class, [
                'required' => false,
                ])
            ->add('promo_skus', TextType::class, [
                'required' => false,
                ])
            ->add('pre_conditions', TextType::class, [
                'required' => false,
                ])
            ->add('target_conditions', TextType::class, [
                'required' => false,
                ])
                ;
    }

    public function getBlockPrefix()
    {
        return 'discount';
    }
}
