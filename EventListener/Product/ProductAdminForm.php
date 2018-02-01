<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Entity\Product;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ProductAdminForm
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductAdminForm
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CurrencyService
     */
    protected $currencyService;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var string
     */
    protected $formTypeClass = '';

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeConfig
     */
    protected $themeConfig;

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
     * @param $currencyService
     * @return $this
     */
    public function setCurrencyService($currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->currencyService;
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
     * @param $themeConfig
     * @return $this
     */
    public function setThemeConfig($themeConfig)
    {
        $this->themeConfig = $themeConfig;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ThemeConfig
     */
    public function getThemeConfig()
    {
        return $this->themeConfig;
    }

    /**
     * @param CoreEvent $event
     */
    public function onProductAdminForm(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $form = $this->getFormFactory()->create($this->getFormTypeClass(), $entity, [
            'action' => $event->getFormAction(),
            'method' => $event->getFormMethod(),
        ]);

        $customFields = [];
        $varSet = $entity->getItemVarSet();
        $vars = $varSet
            ? $varSet->getItemVars()
            : [];

        $varValues = $entity->getVarValues();
        if ($varSet && $vars) {

            foreach($vars as $var) {
                $name = $var->getCode();
                switch($var->getFormInput()) {
                    case 'select':
                    case 'multiselect':
                        $options = $var->getItemVarOptions();
                        $choices = [];
                        if ($options) {
                            foreach($options as $option) {
                                $choices[$option->getValue()] = $option->getValue();
                            }
                        }
                        $form->add($name, ChoiceType::class, [
                            'mapped'    => false,
                            'choices'   => $choices,
                            'required'  => $var->getIsRequired(),
                            'label'     => $var->getName(),
                            'multiple'  => ($var->getFormInput() == EntityConstants::INPUT_MULTISELECT
                                            || ($entity->getType() == Product::TYPE_CONFIGURABLE && $var->getFormInput() == EntityConstants::INPUT_SELECT)),
                        ]);
                        $customFields[] = $name;
                        break;
                    case 'checkbox':
                        $form->add($name, CheckboxType::class, [
                            'mapped' => false,
                            'required' => false,
                            'label' => $var->getName(),
                        ]);
                        $customFields[] = $name;
                        break;
                    default:
                        $form->add($name, TextType::class, [
                            'mapped' => false,
                            'label'  => $var->getName(),
                        ]);
                        $customFields[] = $name;
                        break;
                }
            }

            if ($entity->getId()) {

                $objectVars = [];
                foreach($varValues as $varValue) {
                    $var = $varValue->getItemVar();
                    $name = $var->getCode();
                    $isMultiple = ($var->getFormInput() == EntityConstants::INPUT_MULTISELECT
                                    || ($entity->getType() == Product::TYPE_CONFIGURABLE && $var->getFormInput() == EntityConstants::INPUT_SELECT));

                    $value = ($varValue->getItemVarOption())
                        ? $varValue->getItemVarOption()->getValue()
                        : $varValue->getValue();

                    if (isset($objectVars[$name])) {
                        if ($isMultiple) {
                            $objectVars[$name]['value'][] = $value;
                        }
                    } else {
                        $value = $isMultiple ? [$value] : $value;
                        $objectVars[$name] = [
                            //'var' => $var,
                            'value' => $value,
                            'input' => $var->getFormInput(),
                        ];
                    }
                }

                foreach($objectVars as $name => $objectData) {
                    //$var = $objectData['var'];
                    $value = $objectData['value'];
                    if ($objectData['input'] == 'checkbox') {
                        $value = (bool) $value;
                    }
                    $form->get($name)->setData($value);
                }
            }
        }

        $formSections = [
            'general' => [
                'label' => 'General',
                'id' => 'general',
                'fields' => [
                    'name',
                    'sku',
                    'slug',
                    'price',
                    'is_enabled',
                    'is_public',
                    'is_taxable',
                    'is_discountable',
                ],
            ],
            'stock' => [
                'label'  => 'Stock',
                'id'     => 'stock',
                'fields' => [
                    'is_in_stock',
                    'is_qty_managed',
                    'can_backorder',
                    'qty',
                    'qty_unit',
                    'min_qty',
                    //'stock_type',
                    'upc',
                ],
            ],
            'content' => [
                'label' => 'Content',
                'id' => 'content',
                'fields' => [
                    'content',
                    'page_title',
                    'meta_title',
                    'meta_keywords',
                    'meta_description',
                    'custom_search',
                    'sort_order',
                    'custom_template',
                ],
            ],
            'shipping' => [
                'label' => 'Shipping',
                'id' => 'shipping',
                'fields' => [
                    'is_flat_shipping',
                    'flat_shipping_price',
                    'source_address_key',
                    'weight',
                    'weight_unit',
                    'width',
                    'height',
                    'length',
                    'measure_unit',
                ],
            ],
        ];

        if ($customFields) {

            $formSections['custom'] = [
                'label' => 'Custom',
                'id' => 'custom',
                'fields' => $customFields,
            ];
        }

        $event->setReturnData('form_sections', $formSections);
        $event->setForm($form);
    }
}
