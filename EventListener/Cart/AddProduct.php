<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use MobileCart\CoreBundle\Constants\EntityConstants;

class AddProduct
{
    protected $entityService;

    protected $cartSessionService;

    protected $shippingService;

    protected $router;

    protected $event;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function setCartSessionService($cartSessionService)
    {
        $this->cartSessionService = $cartSessionService;
        return $this;
    }

    public function getCartSessionService()
    {
        return $this->cartSessionService;
    }

    public function setShippingService($shippingService)
    {
        $this->shippingService = $shippingService;
        return $this;
    }

    public function getShippingService()
    {
        return $this->shippingService;
    }

    public function onCartAddProduct(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $success = 0;
        $errors = [];

        $product = null;
        $productId = $request->get('id', '');
        $qty = $request->get('qty', 1);
        $simpleProductId = $request->get('simple_id', '');
        if (!$productId) {
            $productId = $event->get('product_id');
            $qty = $event->get('qty');
        }

        $idField = $request->get('key', 'id');
        $idFields = ['id', 'sku'];
        if (!in_array($idField, $idFields)) {
            $idField = 'id';
        }

        $cart = $this->getCartSessionService()
            ->initCart()
            ->getCart();

        if ($idField != 'id') {
            if ($item = $cart->findItem($idField, $productId)) {
                $productId = $item->getProductId();
            } else {
                $product = $this->getEntityService()->findOneBy(EntityConstants::PRODUCT, [
                    $idField => $productId,
                ]);

                if ($product) {
                    $productId = $product->getId();
                }
            }
        }

        $event->setProductId($productId);
        $cartId = $cart->getId();
        $customerId = $cart->getCustomer()->getId();
        $customerEntity = false;

        $cartEntity = $cartId
            ? $this->getEntityService()->find(EntityConstants::CART, $cartId)
            : $this->getEntityService()->getInstance(EntityConstants::CART);

        if (!$cartId) {

            $cartEntity->setJson($cart->toJson())
                ->setCreatedAt(new \DateTime('now'));

            if ($customerId) {

                $customerEntity = $this->getEntityService()
                    ->find(EntityConstants::CUSTOMER, $customerId);

                if ($customerEntity) {
                    $cartEntity->setCustomer($customerEntity);
                }
            }

            $this->getEntityService()->persist($cartEntity);
            $cartId = $cartEntity->getId();
            $cart->setId($cartId);
        }

        $event->setProductId($productId)
            ->setSimpleProductId($simpleProductId)
            ->setQty($qty);

        if ($this->getCartSessionService()->hasProductId($productId)) {

            $cartItem = $cart->findItem('product_id', $productId);

            // update db
            if ($cartItem) {

                $minQty = (int) $cartItem->getMinQty();
                $availQty = $cartItem->getAvailQty();
                $isQtyManaged = $cartItem->getIsQtyManaged();

                if ($event->getIsAdd()) {

                    $newQty = $cartItem->getQty() + $qty;
                    $minQtyMet = $minQty == 0 || ($minQty > 0 && $newQty >= $minQty);
                    $maxQtyMet = !$isQtyManaged || ($isQtyManaged && $newQty < $availQty);

                    if ($minQtyMet && $maxQtyMet) {

                        $this->getCartSessionService()
                            ->addProductQty($productId, $qty);

                    } else {

                        if (!$minQtyMet) {
                            $errors[] = "Min Qty is not met";
                        }

                        if (!$maxQtyMet) {
                            $errors[] = "Insuffcient stock level";
                        }
                    }
                } else {

                    $minQtyMet = $minQty == 0 || ($minQty > 0 && $qty >= $minQty);
                    $maxQtyMet = !$isQtyManaged || ($isQtyManaged && $qty < $availQty);

                    if ($minQtyMet && $maxQtyMet) {

                        $this->getCartSessionService()
                            ->setProductQty($productId, $qty);

                    } else {

                        if (!$minQtyMet) {
                            $errors[] = "Min Qty is not met";
                        }

                        if (!$maxQtyMet) {
                            $errors[] = "Insuffcient stock level";
                        }
                    }
                }

                $cartItem = $cart->findItem('product_id', $productId);

                if ($cartItem->getId()) {
                    // update row

                    $cartItemEntity = $this->getEntityService()
                        ->find(EntityConstants::CART_ITEM, $cartItem->getId());

                    $cartItemEntity->setQty($cartItem->getQty());

                    $this->getEntityService()->persist($cartItemEntity);

                } else {
                    // insert row

                    $cartItemEntity = $this->getEntityService()
                        ->getInstance(EntityConstants::CART_ITEM);

                    $cartItemEntity->setCart($cartEntity)
                        ->setSku($cartItem->getSku())
                        ->setQty($cartItem->getQty())
                        ->setJson($cartItem->toJson());

                    $this->getEntityService()->persist($cartItemEntity);

                    $cartItem->setId($cartItemEntity->getId());
                }
            }

            $success = 1;

        } else if ($this->getCartSessionService()->hasProductId($simpleProductId)) {

            $cartItem = $cart->findItem('product_id', $simpleProductId);

            // update db
            if ($cartItem) {

                $minQty = (int) $cartItem->getMinQty();
                $availQty = $cartItem->getAvailQty();
                $isQtyManaged = $cartItem->getIsQtyManaged();

                if ($event->getIsAdd()) {

                    $newQty = $cartItem->getQty() + $qty;
                    $minQtyMet = $minQty == 0 || ($minQty > 0 && $newQty >= $minQty);
                    $maxQtyMet = !$isQtyManaged || ($isQtyManaged && $newQty < $availQty);

                    if ($minQtyMet && $maxQtyMet) {

                        $this->getCartSessionService()
                            ->addProductQty($simpleProductId, $qty);

                    } else {

                        if (!$minQtyMet) {
                            $errors[] = "Min Qty is not met";
                        }

                        if (!$maxQtyMet) {
                            $errors[] = "Insuffcient stock level";
                        }
                    }

                } else {

                    $minQtyMet = $minQty == 0 || ($minQty > 0 && $qty >= $minQty);
                    $maxQtyMet = !$isQtyManaged || ($isQtyManaged && $qty < $availQty);

                    if ($minQtyMet && $maxQtyMet) {

                        $this->getCartSessionService()
                            ->setProductQty($simpleProductId, $qty);

                    } else {

                        if (!$minQtyMet) {
                            $errors[] = "Min Qty is not met";
                        }

                        if (!$maxQtyMet) {
                            $errors[] = "Insuffcient stock level";
                        }
                    }
                }

                if ($cartItem->getId()) {
                    // update row

                    $cartItemEntity = $this->getEntityService()
                        ->find(EntityConstants::CART_ITEM, $cartItem->getId());

                    $cartItemEntity->setQty($cartItem->getQty());

                    $this->getEntityService()->persist($cartItemEntity);

                } else {
                    // insert row

                    $cartItemEntity = $this->getEntityService()
                        ->getInstance(EntityConstants::CART_ITEM);

                    $cartItemEntity->setCart($cartEntity)
                        ->setSku($cartItem->getSku())
                        ->setQty($cartItem->getQty())
                        ->setJson($cartItem->toJson());

                    $this->getEntityService()->persist($cartItemEntity);

                    $cartItem->setId($cartItemEntity->getId());
                }
            }

            $success = 1;

        } else if ($productId) {

            // The Cart does not already have this product
            //  so we know this is an insert, not an update

            if ($idField == 'id' && !$product) {
                $product = $this->getEntityService()->find(EntityConstants::PRODUCT, $productId);
            }

            if (!$product) {
                throw new NotFoundHttpException("Product not found with ID: '{$simpleProductId}''");
            }

            $parentOptions = [];
            if ($simpleProductId) {

                $child = $this->getEntityService()->find(EntityConstants::PRODUCT, $simpleProductId);
                if (!$child) {
                    throw new NotFoundHttpException("Child Product not found with ID: '{$simpleProductId}''");
                }

                $minQty = (int) $child->getMinQty();
                $minQtyMet = $minQty == 0 || ($minQty > 0 && $qty >= $child->getMinQty());
                $maxQtyMet = !$child->getIsQtyManaged() || ($child->getIsQtyManaged() && $qty < $child->getQty());

                if ($child->getIsEnabled()
                    && $child->getIsInStock()
                    && $minQtyMet
                    && $maxQtyMet
                ) {

                    $parentOptions['id'] = $product->getId();
                    $parentOptions['sku'] = $product->getSku();
                    $parentOptions['slug'] = $product->getSlug();

                    $this->getCartSessionService()
                        ->addProduct($child, $qty, $parentOptions);

                    $cartItem = $this->getCartSessionService()
                        ->getCart()
                        ->findItem('product_id', $child->getId());

                    if ($cartItem) {

                        $cartItem->setQtyAvail($child->getQty())
                            ->setIsQtyManaged((int) $child->getIsQtyManaged());
                    }

                    $itemJson = $cartItem
                        ? $cartItem->toJson()
                        : json_encode($child->getData());

                    // insert row
                    $cartItemEntity = $this->getEntityService()
                        ->getInstance(EntityConstants::CART_ITEM);

                    $cartItemEntity->setCart($cartEntity)
                        ->setSku($child->getSku())
                        ->setQty($qty)
                        ->setJson($itemJson);

                    $this->getEntityService()->persist($cartItemEntity);

                    $cart->findItem('sku', $child->getSku())
                        ->setId($cartItemEntity->getId());

                    $event->setCartItemEntity($cartItemEntity);

                    $success = 1;

                } else {
                    // add errors to response

                    if (!$child->getIsEnabled()) {
                        $errors[] = "Product is not enabled";
                    }

                    if (!$child->getIsInStock()) {
                        $errors[] = "Product is not in stock";
                    }

                    if (!$minQtyMet) {
                        $errors[] = "Min Qty is not met";
                    }

                    if (!$maxQtyMet) {
                        $errors[] = "Insuffcient stock level";
                    }

                }
            } else {

                $minQty = (int) $product->getMinQty();
                $minQtyMet = $minQty == 0 || ($minQty > 0 && $qty >= $product->getMinQty());
                $maxQtyMet = !$product->getIsQtyManaged() || ($product->getIsQtyManaged() && $qty < $product->getQty());

                if ($product->getIsEnabled()
                    && $product->getIsInStock()
                    && $minQtyMet
                    && $maxQtyMet
                ) {

                    $this->getCartSessionService()
                        ->addProduct($product, $qty, $parentOptions);

                    // insert row
                    $cartItemEntity = $this->getEntityService()
                        ->getInstance(EntityConstants::CART_ITEM);

                    $cartItem = $this->getCartSessionService()
                        ->getCart()
                        ->findItem('product_id', $product->getId());

                    if ($cartItem) {

                        $cartItem->setQtyAvail($product->getQty())
                            ->setIsQtyManaged((int) $product->getIsQtyManaged());
                    }

                    $itemJson = $cartItem
                        ? $cartItem->toJson()
                        : json_encode($product->getData());

                    $cartItemEntity->setCart($cartEntity)
                        ->setSku($product->getSku())
                        ->setQty($qty)
                        ->setJson($itemJson);

                    $this->getEntityService()->persist($cartItemEntity);

                    $cart->findItem('sku', $product->getSku())
                        ->setId($cartItemEntity->getId());

                    $success = 1;
                } else {
                    // add errors to response

                    if (!$product->getIsEnabled()) {
                        $errors[] = "Product is not enabled";
                    }

                    if (!$product->getIsInStock()) {
                        $errors[] = "Product is not in stock";
                    }

                    if (!$minQtyMet) {
                        $errors[] = "Min Qty is not met";
                    }

                    if (!$maxQtyMet) {
                        $errors[] = "Insuffcient stock level";
                    }
                }
            }
        }

        $cart = $this->getCartSessionService()
            ->collectShippingMethods()
            ->collectTotals()
            ->getCart();

        // update db
        $cartEntity->setJson($cart->toJson())
            ->setCreatedAt(new \DateTime('now'));

        if ($customerId && !$cartEntity->getCustomer()) {

            if (!$customerEntity) {

                $customerEntity = $this->getEntityService()
                    ->find(EntityConstants::CUSTOMER, $customerId);

            }

            $cartEntity->setCustomer($customerEntity);
        }

        $this->getEntityService()->persist($cartEntity);
        $event->setCartEntity($cartEntity);
        $cartId = $cartEntity->getId();
        $cart->setId($cartId);

        $returnData['cart'] = $cart;
        $returnData['success'] = $success;
        $returnData['errors'] = $errors;

        $response = '';
        switch($format) {
            case 'json':
                $response = new JsonResponse($returnData);
                break;
            default:
                $params = [];
                $route = 'cart_view';
                $url = $this->getRouter()->generate($route, $params);
                $response = new RedirectResponse($url);
                break;
        }

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }
}
