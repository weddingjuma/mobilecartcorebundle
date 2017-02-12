<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use MobileCart\CoreBundle\Constants\EntityConstants;

class AddProductV2
{
    /**
     * @var \MobileCart\CoreBundle\Service\DoctrineEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    protected $cartSessionService;

    protected $router;

    protected $event;

    protected $cartEntity;

    protected $customerEntity;

    protected $cartItem;

    protected $cartItemEntity;

    protected $product;

    protected $productId;

    protected $isAdd = true;

    protected $hasTierPriceChange = false;

    protected $qty = 1;

    protected $totalQty = 1;

    protected $errors = [];

    protected $success = 0;

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

    public function initCartEntity()
    {
        $cart = $this->getCartSessionService()->getCart();
        $cartId = $cart->getId();
        $customerId = $cart->getCustomer()->getId();

        $cartEntity = $cartId
            ? $this->getEntityService()->find(EntityConstants::CART, $cartId)
            : $this->getEntityService()->getInstance(EntityConstants::CART);

        if (!$cartId) {

            $cartEntity->setJson($cart->toJson())
                ->setCreatedAt(new \DateTime('now'));

        }

        if ($customerId) {

            $customerEntity = $this->getEntityService()
                ->find(EntityConstants::CUSTOMER, $customerId);

            if ($customerEntity) {
                $cartEntity->setCustomer($customerEntity);

            }
        }

        // always save the cart
        $this->getEntityService()->persist($cartEntity);
        $cartId = $cartEntity->getId();
        $cart->setId($cartId);

        $this->setCartEntity($cartEntity);
        return $this;
    }

    public function setCartEntity($cartEntity)
    {
        $this->cartEntity = $cartEntity;
        return $this;
    }

    public function getCartEntity()
    {
        return $this->cartEntity;
    }

    public function setCustomerEntity($customerEntity)
    {
        $this->customerEntity = $customerEntity;
        return $this;
    }

    public function getCustomerEntity()
    {
        return $this->customerEntity;
    }

    public function loadProduct($value, $idField = 'id')
    {
        return $this->getEntityService()->findOneBy(EntityConstants::PRODUCT, [
            $idField => $value,
        ]);
    }

    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    public function getProductId()
    {
        return $this->productId;
    }

    public function setQty($qty)
    {
        $this->qty = $qty;
        return $this;
    }

    public function getQty()
    {
        return $this->qty;
    }

    public function setTotalQty($totalQty)
    {
        $this->totalQty = $totalQty;
        return $this;
    }

    public function getTotalQty()
    {
        return $this->totalQty;
    }

    public function setIsAdd($isAdd)
    {
        $this->isAdd = $isAdd;
        return $this;
    }

    public function getIsAdd()
    {
        return $this->isAdd;
    }

    public function setHasTierPriceChange($yesNo)
    {
        $this->hasTierPriceChange = $yesNo;
        return $this;
    }

    public function getHasTierPriceChange()
    {
        return $this->hasTierPriceChange;
    }

    public function setErrors(array $errors)
    {
        $this->errors = $errors;
        return $this;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setCartItem($cartItem)
    {
        $this->cartItem = $cartItem;
        return $this;
    }

    public function getCartItem()
    {
        return $this->cartItem;
    }

    public function setCartItemEntity($cartItemEntity)
    {
        $this->cartItemEntity = $cartItemEntity;
        return $this;
    }

    public function getCartItemEntity()
    {
        return $this->cartItemEntity;
    }

    public function setSuccess($success)
    {
        $this->success = $success;
        return $this;
    }

    public function getSuccess()
    {
        return $this->success;
    }

    public function meetsCriteria($cartItem)
    {
        $qty = $this->getQty();
        $errors = [];
        $minQty = (int) $cartItem->getMinQty();
        $availQty = $cartItem->getAvailQty();
        $isQtyManaged = $cartItem->getIsQtyManaged();

        $newQty = $cartItem->getQty() + $qty;
        $minQtyMet = $minQty == 0 || ($minQty > 0 && $newQty >= $minQty);
        $maxQtyMet = !$isQtyManaged || ($isQtyManaged && $newQty < $availQty);

        if (!$cartItem->getIsEnabled()) {
            $errors[] = "Product is not enabled : {$cartItem->getSku()}";
        }

        if (!$cartItem->getIsInStock()) {
            $errors[] = "Product is not in stock : {$cartItem->getSku()}";
        }

        if (!$minQtyMet) {
            $errors[] = "Minimum Qty is not met : {$cartItem->getSku()}, Qty: {$cartItem->getMinQty()}";
        }

        if (!$maxQtyMet) {
            $errors[] = "Insufficient stock level : {$cartItem->getSku()}, Available: {$cartItem->getAvailQty()}";
        }

        $this->setErrors($errors);
        return count($errors) == 0;
    }

    public function updateTierPrice(&$cartItem)
    {
        if (!$cartItem || !is_object($cartItem)) {
            return $this;
        }

        $qty = $cartItem->getQty();

        if ($cartItem->getTierPrices()
            && is_array($cartItem->getTierPrices())
        ) {

            $meetsTier = false;
            $tierPrices = $cartItem->getTierPrices();
            if ($tierPrices) {
                $lastQty = 0;
                foreach($tierPrices as $tierPrice) {

                    if ($tierPrice instanceof \stdClass) {
                        $tierPrice = get_object_vars($tierPrice);
                    }

                    if (is_array($tierPrice)) {
                        $tierPrice = new ArrayWrapper($tierPrice);
                    }

                    if ($qty > $lastQty && $qty >= $tierPrice->getQty()) {
                        $lastQty = $tierPrice->getQty();
                        $cartItem->setPrice($tierPrice->getPrice());
                        $this->setHasTierPriceChange(true);
                        $meetsTier = true;
                    }
                }
            }

            if (!$meetsTier) {
                $cartItem->setPrice($cartItem->getOrigPrice());
                $this->setHasTierPriceChange(true);
            }
        }

        return $this;
    }

    public function saveCartItem()
    {
        $cartItem = $this->getCartItem();

        $customerAddressId = $cartItem->get('customer_address_id', 'main');
        if (is_numeric($customerAddressId)) {
            $customerAddressId = (int) $customerAddressId;
        } else if (is_int(strpos($customerAddressId, '_'))) {
            $addressParts = explode('_', $cartItem->getCustomerAddressId()); // eg 'address_3'
            $customerAddressId = count($addressParts) == 2
                ? $addressParts[1]
                : null;
        } elseif ($customerAddressId == 'main') {
            $customerAddressId = null;
        }

        $currencyService = $this->getCartSessionService()->getCurrencyService();
        $baseCurrency = $this->getCartSessionService()->getBaseCurrency();
        $customerCurrency = $this->getCartSessionService()->getCurrency();
        if (!strlen($customerCurrency)) {
            $customerCurrency = $baseCurrency;
        }

        $cartItemEntity = $this->getEntityService()
            ->getInstance(EntityConstants::CART_ITEM);

        // get or create cartItemEntity

        if ($cartItem->getId()) {

            $cartItemEntity = $this->getEntityService()
                ->find(EntityConstants::CART_ITEM, $cartItem->getId());

            if (!$cartItemEntity) {
                throw new \Exception("Something terrible happened.");
            }

            // update customer_address_id , if necessary
            if ($cartItemEntity->getCustomerAddressId() != $customerAddressId) {
                $cartItemEntity->setCustomerAddressId($customerAddressId);
            }

        } else {

            $itemJson = $cartItem
                ? $cartItem->toJson()
                : '{}';

            $cartItemEntity
                ->setCart($this->getCartEntity())
                ->setCreatedAt(new \DateTime('now'))
                ->setSku($cartItem->getSku())
                ->setProductId($cartItem->getProductId())
                ->setQty($cartItem->getQty())
                ->setWeight($cartItem->getWeight())
                ->setWeightUnit($cartItem->getWeightUnit())
                ->setWidth($cartItem->getWidth())
                ->setHeight($cartItem->getHeight())
                ->setLength($cartItem->getLength())
                ->setMeasureUnit($cartItem->getMeasureUnit())
                ->setCustomerAddressId($customerAddressId)
                ->setSourceAddressKey($cartItem->getSourceAddressKey())
                ->setJson($itemJson);
        }

        if ($cartItemEntity) {

            $cartItemEntity->setQty($cartItem->getQty());

            // if existing row with price change, or inserting new row
            if (
                ($cartItemEntity->getId() && $this->getHasTierPriceChange())
                || !$cartItemEntity->getId()
            ) {

                $productCurrency = $cartItem->getCurrency();
                if (!$productCurrency) {
                    $productCurrency = $baseCurrency;
                }

                if ($baseCurrency == $productCurrency) {
                    if ($customerCurrency == $baseCurrency) {

                        $cartItemEntity->setPrice($cartItem->getPrice())
                            //->setTax() todo : update this during total collection, tax collector, create a tax grid function
                            //->setDiscount() todo : update this during total collection, retrieve from the discount grid function
                            ->setCurrency($baseCurrency)
                            ->setBasePrice($cartItem->getPrice())
                            //->setBaseTax() todo
                            //->setBaseDiscount() todo
                            ->setBaseCurrency($baseCurrency);

                    } else {

                        $cartItemEntity->setPrice($currencyService->convert($cartItem->getPrice(), $customerCurrency))
                            //->setTax() todo
                            //->setDiscount() todo
                            ->setCurrency($customerCurrency)
                            ->setBasePrice($cartItem->getPrice())
                            //->setBaseTax() todo
                            //->setBaseDiscount() todo
                            ->setBaseCurrency($baseCurrency);

                    }
                } else {
                    if ($productCurrency == $customerCurrency) {

                        $cartItemEntity->setPrice($cartItem->getPrice())
                            //->setTax() todo
                            //->setDiscount() todo
                            ->setCurrency($customerCurrency)
                            ->setBasePrice($currencyService->convert($cartItem->getPrice(), $baseCurrency, $customerCurrency))
                            //->setBaseTax() todo
                            //->setBaseDiscount() todo
                            ->setBaseCurrency($baseCurrency);

                    } else {

                        $cartItemEntity->setPrice($currencyService->convert($cartItem->getPrice(), $customerCurrency, $productCurrency))
                            //->setTax() todo
                            //->setDiscount() todo
                            ->setCurrency($customerCurrency)
                            ->setBasePrice($currencyService->convert($cartItem->getPrice(), $baseCurrency, $productCurrency))
                            //->setBaseTax() todo
                            //->setBaseDiscount() todo
                            ->setBaseCurrency($baseCurrency);
                    }
                }
            }

            $this->getEntityService()->persist($cartItemEntity);
            $cartItem->setId($cartItemEntity->getId());
            $this->setCartItemEntity($cartItemEntity);
        }

        return $this;
    }

    /**
     * @param $cartItem
     * @param array $recollectShipping
     * @return $this
     */
    public function collectAddresses(&$cartItem, array &$recollectShipping)
    {
        $event = $this->getEvent();
        $productId = $cartItem->getProductId();
        $request = $event->getRequest();
        $productAddresses = $request->get('product_address', []);

        // update shipping address
        if ($event->getIsMultiShippingEnabled()) {

            // get customer_address_id from request
            if (isset($productAddresses[$productId])) {

                $customerAddressId = $productAddresses[$productId];
                if ($customerAddressId != 'main' && is_numeric($customerAddressId)) {
                    $customerAddressId = 'address_' . $customerAddressId;
                }

                if ($cartItem->get('customer_address_id', 'main') != $customerAddressId) {

                    // recollect the original shipping address, since the items have changed for that address
                    $recollectShipping[] = new ArrayWrapper([
                        'customer_address_id' => $cartItem->get('customer_address_id', 'main'),
                        'source_address_key' => $cartItem->get('source_address_key', 'main')
                    ]);

                    // update cart item
                    $cartItem->set('customer_address_id', $customerAddressId);
                }

                // recollect new shipping address
                $recollectShipping[] = new ArrayWrapper([
                    'customer_address_id' => $cartItem->get('customer_address_id', $customerAddressId),
                    'source_address_key' => $cartItem->get('source_address_key', 'main')
                ]);

            } else {

                // we can assume the address for the cart item is not changing
                //  but we need to recollect in case qty or weight changed

                $recollectShipping[] = new ArrayWrapper([
                    'customer_address_id' => $cartItem->get('customer_address_id', 'main'),
                    'source_address_key' => $cartItem->get('source_address_key', 'main')
                ]);
            }

        } else {

            // always recollect main address when multi shipping is disabled

            $recollectShipping[] = new ArrayWrapper([
                'customer_address_id' => $cartItem->get('customer_address_id', 'main'),
                'source_address_key' => $cartItem->get('source_address_key', 'main')
            ]);
        }

        return $this;
    }

    public function onCartAddProduct(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $recollectShipping = []; // r = [object, object] , object:{'customer_address_id':'','source_address_key':''}

        $success = 0;
        $errors = [];
        $cartItemEntity = null;
        $product = null;

        $slug = '';
        $cart = $this->getCartSessionService()->getCart();

        $qty = $request->get('qty', 1);

        // keyValue could be a sku
        $keyValue = $request->get('id', '');
        $keyField = $request->get('key', 'id');
        $keyFields = ['id', 'sku']; // possibly allow more in the future
        $productId = 0; // dont set productId until we know the valid value

        $parentProductId = $request->get('id', ''); // always the case: integer , parent product_id
        $parentOptions = [];

        // check if parameters passed via Event
        if (!$keyValue && $event->get('product_id')) {
            $qty = $event->get('qty');
            $keyValue = $event->get('product_id');
            $keyField = 'id';
        }

        // check if product is a child product
        $simpleProductId = $request->get('simple_id', '');
        if ($simpleProductId) {
            $keyValue = $simpleProductId;
            $keyField = 'id';
        } else {
            if (!in_array($keyField, $keyFields)) {
                $keyField = 'id';
            }
        }

        // check if we already have the product in the cart
        if ($item = $cart->findItem($keyField, $keyValue)) {
            $productId = $item->getProductId();
            $slug = $item->getSlug();
            $this->setCartItem($item);
        } else {

            // otherwise, load the product
            if ($product = $this->loadProduct($keyValue, $keyField)) {
                $productId = $product->getId();
                $slug = $product->getSlug();
                $this->setProduct($product);
            }

            // don't execute a query unless we have simpleProductId, and a product
            if ($simpleProductId && $product) {
                // load product from entity service
                if ($parent = $this->loadProduct($parentProductId)) {
                    $parentOptions['id'] = $parent->getId();
                    $parentOptions['sku'] = $parent->getSku();
                    $parentOptions['slug'] = $parent->getSlug();
                }
            }
        }

        // we MUST have a Product ID
        if (!$productId) {
            return;
        }

        $this->setProductId($productId)
            ->setQty($qty)
            ->setIsAdd($event->getIsAdd());

        $cartEntity = $this->initCartEntity()
            ->getCartEntity();

        $event->setProductId($productId)
            ->setSimpleProductId($simpleProductId)
            ->setCartEntity($cartEntity)
            ->setQty($qty);

        $this->setEvent($event);

        /*

        LOGIC OVERVIEW:

        1. look for simple product ID . we may already have an item in the cart with the same parent ID, but different simple product ID
        2. look for product ID
        3. update shipping addresses
        4.

        //*/

        if ($simpleProductId) {
            // we have a simple product, and its already in the cart
            if ($this->getCartSessionService()->hasProductId($simpleProductId)) {

                // todo : find by item id
                $cartItem = $cart->findItem('product_id', $simpleProductId);
                if ($event->getIsAdd()) {
                    $this->setTotalQty($qty + $cartItem->getQty());
                } else {
                    $this->setTotalQty($qty);
                }

                // check criteria
                if ($cartItem
                    && $this->meetsCriteria($cartItem)
                ) {

                    // update quantity
                    $this->getCartSessionService()
                        ->setProductQty($simpleProductId, $this->getTotalQty());

                    // update tier price
                    $this->updateTierPrice($cartItem);

                    // update shipping address
                    $this->collectAddresses($cartItem, $recollectShipping);

                    // update cart item and totals
                    $this->setCartItem($cartItem)->saveCartItem();
                }

            } else {

                // we have a simple product, but it's not in the cart yet

                // create cart item with loaded product
                $cartItem = $this->getCartSessionService()
                    ->createCartItem($this->getProduct(), $parentOptions)
                    ->setQty($qty)
                    ->setQtyAvail($this->getProduct()->getQty())
                    ->setIsQtyManaged((int) $this->getProduct()->getIsQtyManaged())
                    ->setCustomerAddressId('main');

                $this->setTotalQty($qty);

                // check criteria
                if ($this->meetsCriteria($cartItem)) {
                    // update tier price
                    $this->updateTierPrice($cartItem);

                    // add to cart
                    $this->getCartSessionService()->addItem($cartItem, $qty);

                    // update shipping address
                    $this->collectAddresses($cartItem, $recollectShipping);

                    // update cart totals
                    $this->setCartItem($cartItem)->saveCartItem();
                }
            }
        } else {
            if ($this->getCartSessionService()->hasProductId($productId)) {

                // todo : find by item id
                $cartItem = $cart->findItem('product_id', $productId);
                if ($event->getIsAdd()) {
                    $this->setTotalQty($qty + $cartItem->getQty());
                } else {
                    $this->setTotalQty($qty);
                }

                // check criteria
                if ($cartItem
                    && $this->meetsCriteria($cartItem)
                ) {

                    // update quantity
                    $this->getCartSessionService()
                        ->setProductQty($productId, $this->getTotalQty());

                    // update tier price
                    $this->updateTierPrice($cartItem);

                    // update shipping address
                    $this->collectAddresses($cartItem, $recollectShipping);

                    // update cart item and totals
                    $this->setCartItem($cartItem)->saveCartItem();
                }

            } else {

                // create cart item with loaded product
                $cartItem = $this->getCartSessionService()
                    ->createCartItem($this->getProduct())
                    ->setQty($qty)
                    ->setQtyAvail($this->getProduct()->getQty())
                    ->setIsQtyManaged((int) $this->getProduct()->getIsQtyManaged())
                    ->setCustomerAddressId('main');

                $this->setTotalQty($qty);

                // check criteria
                if ($this->meetsCriteria($cartItem)) {

                    // update tier price
                    $this->updateTierPrice($cartItem);

                    // add to cart
                    $this->getCartSessionService()->addItem($cartItem, $qty);

                    // update shipping address
                    $this->collectAddresses($cartItem, $recollectShipping);

                    // update cart totals
                    $this->setCartItem($cartItem)->saveCartItem();
                }
            }
        }

        $event->setRecollectShipping($recollectShipping);
        $event->setCartItemEntity($this->getCartItemEntity());
        $returnData['cart'] = $this->getCartSessionService()->getCart();
        $returnData['success'] = $this->getSuccess();
        $returnData['errors'] = $this->getErrors();

        $response = '';
        switch($format) {
            case 'json':
                $response = new JsonResponse($returnData);
                break;
            default:

                $route = 'cart_view';
                $params = [];

                if ($errors) {
                    foreach($errors as $error) {
                        $request->getSession()->getFlashBag()->add(
                            CoreEvent::MSG_ERROR,
                            $error
                        );
                    }

                    if ($slug && $event->getIsAdd()) {
                        $route = 'cart_product_view';
                        $params = ['slug' => $this->getCartItem()->getSlug()];
                    }
                } elseif ($success && $event->getIsAdd()) {

                    $request->getSession()->getFlashBag()->add(
                        'success',
                        'Product Added to Cart'
                    );
                }

                $url = $this->getRouter()->generate($route, $params);
                $response = new RedirectResponse($url);
                break;
        }

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }
}
