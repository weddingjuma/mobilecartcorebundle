<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Entity\Product;

/**
 * Class ProductEditReturn
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductEditReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\ImageService
     */
    protected $imageService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @param \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     * @return $this
     */
    public function setEntityService(\MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface $entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param $imageService
     * @return $this
     */
    public function setImageService($imageService)
    {
        $this->imageService = $imageService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ImageService
     */
    public function getImageService()
    {
        return $this->imageService;
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
    public function onProductEditReturn(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $config = @ (array) json_decode($entity->getConfig());
        $typeSections = [];
        $objectType = EntityConstants::PRODUCT;

        $typeSections['images'] = [
            'section_id'   => 'images',
            'label'        => 'Images',
            'template'     => $this->getThemeService()->getAdminTemplatePath() . 'Widgets/Image:uploader.html.twig',
            'js_template'  => $this->getThemeService()->getAdminTemplatePath() . 'Widgets/Image:uploader_js.html.twig',
            'images'       => $entity->getImages(),
            'image_sizes'  => $this->getImageService()->getImageConfigs($objectType),
            'upload_query' => "?object_type={$objectType}&object_id={$entity->getId()}",
        ];

        $categories = [];
        $categoryIds = [];
        $productCats = $entity->getCategoryProducts();
        if ($productCats) {
            foreach($productCats as $productCat) {
                $category = $productCat->getCategory();
                $categoryData = $category->getBaseData();
                foreach($categoryData as $k => $v) {
                    if (is_array($v)) {
                        unset($categoryData[$k]);
                    }
                }
                $categories[] = $categoryData;
                $categoryIds[] = $category->getId();
            }
        }

        // todo : retrieve categories with a single query using categoryIds

        $typeSections['categories'] = [
            'section_id'   => 'categories',
            'label'        => 'Categories',
            'template'     => $this->getThemeService()->getAdminTemplatePath() . 'Product/Category:category_tabs.html.twig',
            'js_template'  => $this->getThemeService()->getAdminTemplatePath() . 'Product/Category:category_tabs_js.html.twig',
            'categories'   => $categories,
            'category_ids' => $categoryIds,
        ];

        /*
        $typeSections['relatedproducts'] = array(
            'section_id'  => 'relatedproducts',
            'label'       => 'Related Products',
            'template'    => $this->getThemeService()->getAdminTemplatePath() . 'Product/Type:product_grid_related_tabs.html.twig',
            'js_template' => $this->getThemeService()->getAdminTemplatePath() . 'Product/Type:product_grid_related_js.html.twig',
            'child_ids'    => [],
            'check_prefix' => 'related-id-', // for shared templates in product-listing.js, todo: remove this
        ); //*/

        switch($entity->getType()) {
            case Product::TYPE_SIMPLE:

                break;
            case Product::TYPE_CONFIGURABLE:
                $varSet = $entity->getItemVarSet();
                $vars = $varSet
                    ? $varSet->getItemVars()
                    : [];

                $varCodes = [];
                if (isset($config['config_values'])) {
                    foreach($config['config_values'] as $configValue) {
                        $configValue = get_object_vars($configValue);
                        $varCodes[] = isset($configValue['var_code']) ? $configValue['var_code'] : '';
                    }
                }

                foreach($vars as $code => $var) {
                    if (in_array($code, $varCodes)) {
                        $var = $vars[$code];
                        $var->checked = 1;
                        $vars[$code] = $var;
                    }
                }

                $childIds = [];
                $productConfigs = $entity->getProductConfigs();
                if ($productConfigs->count()) {
                    foreach($productConfigs as $productConfig) {
                        $childId = $productConfig->getChildProduct()->getId();
                        // enforcing distinct / unique values in array
                        $childIds[$childId] = $childId;
                    }
                    $childIds = array_keys($childIds);
                }

                $typeSections['configproducts'] = [
                    'section_id'   => 'configproducts',
                    'label'        => 'Configured Products',
                    'template'     => $this->getThemeService()->getAdminTemplatePath() . 'Product/Type:product_grid_config_tabs.html.twig',
                    'js_template'  => $this->getThemeService()->getAdminTemplatePath() . 'Product/Type:product_grid_config_js.html.twig',
                    'vars'         => $vars,
                    'child_ids'    => $childIds,
                    'check_prefix' => 'child-id-', // for shared templates in product-listing.js, todo: remove this
                ];

                $event->setReturnData('child_products', $this->getEntityService()->findBy(EntityConstants::PRODUCT, [
                    'id' => $childIds
                ]));

                break;
            default:

                break;
        }

        $event->setReturnData('entity', $entity);
        $event->setReturnData('form', $event->getForm()->createView());
        $event->setReturnData('template_sections', $typeSections);

        $event->setResponse($this->getThemeService()->renderAdmin(
            'Product:edit.html.twig',
            $event->getReturnData()
        ));
    }
}
