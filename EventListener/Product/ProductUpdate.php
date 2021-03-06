<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Entity\Product;

/**
 * Class ProductUpdate
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductUpdate
{
    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

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
     * @param CoreEvent $event
     */
    public function onProductUpdate(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\Product $entity */
        $entity = $event->getEntity();
        $formData = $event->getFormData();
        $request = $event->getRequest();

        $fulltextData = $entity->getBaseData();
        foreach($fulltextData as $k => $v) {
            if (is_numeric($fulltextData[$k]) || is_array($v) || is_object($v)) {
                unset($fulltextData[$k]);
            }
        }

        $entity->setSlug($this->getEntityService()->slugify($entity->getSlug()));

        $this->getEntityService()->beginTransaction();

        try {
            $this->getEntityService()->persist($entity);
            if ($entity->getItemVarSet() && $formData) {
                $this->getEntityService()->persistVariants($entity, $formData);
            }
            $this->getEntityService()->commit();
            $event->setSuccess(true);
            $event->addSuccessMessage('Product Updated !');
        } catch(\Exception $e) {
            $this->getEntityService()->rollBack();
            $event->setSuccess(false);
            $event->addErrorMessage('An error occurred while saving the Product');
            return;
        }

        // ensure configurable product is configured correctly
        if ($entity->getType() == Product::TYPE_CONFIGURABLE) {

            // get current config
            $productVariantCodes = [];
            $pConfigs = $entity->getProductConfigs(); // current
            $newConfigs = []; // new
            if ($pConfigs) {
                foreach($pConfigs as $tmpConfig) {
                    $childProductId = $tmpConfig->getChildProduct()->getId();
                    $varCode = $tmpConfig->getItemVar()->getCode();
                    $productVariantCodes[$childProductId][$varCode] = $tmpConfig;
                }
            }

            $simpleIds = is_array($request->get('simple_ids', []))
                ? $request->get('simple_ids', [])
                : [];

            $variantCodes = $request->get('config_vars', []);

            if ($simpleIds && $variantCodes) {

                // load variants
                $variants = $this->getEntityService()->findBy(EntityConstants::ITEM_VAR, [
                    'code' => $variantCodes
                ]);

                // load products
                $simples = $this->getEntityService()->findBy(EntityConstants::PRODUCT, [
                    'id' => $simpleIds,
                ]);

                if ($simples && $variants) {

                    foreach($simples as $simple) {
                        foreach($variants as $itemVar) {

                            $childProductId = $simple->getId();
                            $varCode = $itemVar->getCode();

                            if (isset($productVariantCodes[$childProductId][$varCode])) {
                                // already have it
                                $newConfigs[] = $productVariantCodes[$childProductId][$varCode];
                                //  unset it, and whatever is left will be deleted
                                unset($productVariantCodes[$childProductId][$varCode]);
                            } else {
                                // dont already have it
                                //  create it, and dont add to $productVariantCodes
                                $pConfig = $this->getEntityService()->getInstance(EntityConstants::PRODUCT_CONFIG);
                                $pConfig->setProduct($entity)
                                    ->setChildProduct($simple)
                                    ->setItemVar($itemVar);

                                try {
                                    $this->getEntityService()->persist($pConfig);
                                } catch(\Exception $e) {
                                    $this->getEntityService()->rollBack();
                                    $event->setSuccess(false);
                                    $event->addErrorMessage('An error occurred while saving the Product configuration');
                                    return;
                                }

                                $newConfigs[] = $pConfig;
                            }

                            $simpleValue = $simple->getData($itemVar->getCode());
                            if (isset($formData[$itemVar->getCode()])) {

                                if (!is_array($formData[$itemVar->getCode()])) {
                                    $aValue = $formData[$itemVar->getCode()];
                                    $formData[$itemVar->getCode()] = [$aValue];
                                }

                                if (!in_array($simpleValue, $formData[$itemVar->getCode()])) {
                                    $formData[$itemVar->getCode()][] = $simpleValue;
                                }

                            } else {
                                $formData[$itemVar->getCode()] = [$simpleValue];
                            }
                        }
                    }

                    // delete whats left
                    if ($productVariantCodes) {
                        foreach($productVariantCodes as $childProductId => $pConfigs) {
                            if ($pConfigs) {
                                foreach($pConfigs as $varCode => $pConfig) {
                                    try {
                                        $this->getEntityService()->remove($pConfig);
                                    } catch(\Exception $e) {
                                        $this->getEntityService()->rollBack();
                                        $event->setSuccess(false);
                                        $event->addErrorMessage('An error occurred while saving the Product configuration');
                                        return;
                                    }
                                }
                            }
                        }
                    }

                } else {
                    if ($pConfigs) {
                        foreach($pConfigs as $pConfig) {
                            try {
                                $this->getEntityService()->remove($pConfig);
                            } catch(\Exception $e) {
                                $this->getEntityService()->rollBack();
                                $event->setSuccess(false);
                                $event->addErrorMessage('An error occurred while saving the Product configuration');
                                return;
                            }
                        }
                    }
                }
            } else {
                if ($pConfigs) {
                    foreach($pConfigs as $pConfig) {
                        try {
                            $this->getEntityService()->remove($pConfig);
                        } catch(\Exception $e) {
                            $this->getEntityService()->rollBack();
                            $event->setSuccess(false);
                            $event->addErrorMessage('An error occurred while saving the Product configuration');
                            return;
                        }
                    }
                }
            }

            $entity->setProductConfigs($newConfigs);
            $entity->reconfigure();

            try {
                $this->getEntityService()->persist($entity);
            } catch(\Exception $e) {
                $this->getEntityService()->rollBack();
                $event->setSuccess(false);
                $event->addErrorMessage('An error occurred while saving the Product');
                return;
            }
        }

        // update categories
        $categoryIds = $entity->getCategoryIds();
        $postedIds = $request->get('category_ids', []);
        $removed = array_diff($categoryIds, $postedIds);
        $added = array_diff($postedIds, $categoryIds);

        if ($removed) {
            foreach($entity->getCategoryProducts() as $categoryProduct) {
                if (in_array($categoryProduct->getCategory()->getId(), $removed)) {
                    try {
                        $this->getEntityService()->remove($categoryProduct);
                    } catch(\Exception $e) {
                        $event->addErrorMessage('An error occurred while removing a Product Category association');
                        // this isn't a 'critical error'
                    }
                }
            }
        }

        if ($added) {
            foreach($added as $categoryId) {
                $categoryProduct = $this->getEntityService()->getInstance(EntityConstants::CATEGORY_PRODUCT);
                $category = $this->getEntityService()->find(EntityConstants::CATEGORY, $categoryId);
                if ($category) {
                    $categoryProduct->setCategory($category);
                    $categoryProduct->setProduct($entity);
                    try {
                        $this->getEntityService()->persist($categoryProduct);
                    } catch(\Exception $e) {
                        $event->addErrorMessage('An error occurred while saving a Product Category association');
                        // this isn't a 'critical error'
                    }
                }
            }
        }

        // update images
        if ($imageJson = $request->get('images_json', [])) {
            $images = (array) @ json_decode($imageJson);
            if ($images) {
                try {
                    $this->getEntityService()->updateImages(EntityConstants::PRODUCT_IMAGE, $entity, $images);
                } catch(\Exception $e) {
                    $event->addErrorMessage('An error occurred while saving a Product image association');
                    // this isn't a 'critical error'
                }
            }
        }
    }
}
