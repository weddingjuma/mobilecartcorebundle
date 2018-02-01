<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class AddProduct
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class AddProduct extends BaseCartListener
{
    /**
     * @var \MobileCart\CoreBundle\Entity\Product
     */
    protected $product;

    /**
     * @var int|null
     */
    protected $productId;

    /**
     * @var bool
     */
    protected $isAdd = true;

    /**
     * @var bool
     */
    protected $enableQtyCheck = true;

    /**
     * @var int
     */
    protected $qty = 1;

    /**
     * @var bool
     */
    protected $success = false;

    /**
     * @return \MobileCart\CoreBundle\Service\DoctrineEntityService
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function loadProduct($key, $value)
    {
        if ($key == 'product_id') {
            return $this->getEntityService()->find(EntityConstants::PRODUCT, $value);
        }

        return $this->getEntityService()->findOneBy(EntityConstants::PRODUCT, [
            $key => $value,
        ]);
    }

    /**
     * @param $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param $qty
     * @return $this
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
        return $this;
    }

    /**
     * @return int
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param $isAdd
     * @return $this
     */
    public function setIsAdd($isAdd)
    {
        $this->isAdd = $isAdd;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAdd()
    {
        return $this->isAdd;
    }

    /**
     * @param $success
     * @return $this
     */
    public function setSuccess($success)
    {
        $this->success = $success;
        return $this;
    }

    /**
     * @return int
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setEnableQtyCheck($yesNo)
    {
        $this->enableQtyCheck = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnableQtyCheck()
    {
        return $this->enableQtyCheck;
    }

    /**
     * @param $item
     * @param $totalQty
     * @param CoreEvent $event
     * @return bool
     */
    public function meetsCriteria(\MobileCart\CoreBundle\CartComponent\Item $item, $totalQty, CoreEvent &$event)
    {
        $minQty = (int) $item->getMinQty();
        $availQty = (int) $item->getAvailQty();
        $isQtyManaged = (bool) $item->getIsQtyManaged();

        $minQtyMet = $minQty == 0 || ($minQty > 0 && $totalQty >= $minQty);
        $maxQtyMet = !$isQtyManaged || ($isQtyManaged && $totalQty < $availQty);

        if (!$item->getIsEnabled()) {
            $event->addErrorMessage("Product is not enabled : {$item->getSku()}");
            return false;
        }

        if (!$item->getIsInStock()) {
            $event->addErrorMessage("Product is not in stock : {$item->getSku()}");
            return false;
        }

        if ($item->getPromoQty() > 0 && $totalQty !== $item->getPromoQty()) {
            $event->addInfoMessage('Cannot change qty on promo item');
            return false;
        }

        if ($this->getEnableQtyCheck()) {

            if (!$minQtyMet) {
                $event->addErrorMessage("Minimum Qty is not met : {$item->getSku()}, Qty: {$item->getMinQty()}");
                return false;
            }

            if (!$maxQtyMet) {
                $event->addErrorMessage("Insufficient stock level : {$item->getSku()}, Available: {$item->getAvailQty()}");
                return false;
            }
        }

        return true;
    }

    /**
     * @param $cartItem
     * @return $this
     */
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

    /**
     * @param $event
     * @param $cartItem
     * @param array $recollectShipping
     * @return $this
     */
    public function collectAddresses($event, &$cartItem, array &$recollectShipping)
    {
        $productId = $cartItem->getProductId();
        $request = $event->getRequest();
        $productAddresses = $request->get('product_address', []);

        // update shipping address
        if ($this->getCartService()->getIsMultiShippingEnabled()) {

            // get customer_address_id from request
            if (isset($productAddresses[$productId])) {

                $customerAddressId = $this->getCartService()->prefixAddressId($productAddresses[$productId]);

                if ($cartItem->get(EntityConstants::CUSTOMER_ADDRESS_ID, 'main') != $customerAddressId) {

                    // recollect the original shipping address, since the items have changed for that address
                    $recollectShipping[] = new ArrayWrapper([
                        EntityConstants::CUSTOMER_ADDRESS_ID => $cartItem->get(EntityConstants::CUSTOMER_ADDRESS_ID, 'main'),
                        EntityConstants::SOURCE_ADDRESS_KEY => $cartItem->get(EntityConstants::SOURCE_ADDRESS_KEY, 'main')
                    ]);

                    // update cart item
                    $cartItem->set(EntityConstants::CUSTOMER_ADDRESS_ID, $customerAddressId);
                }

                // recollect new shipping address
                $recollectShipping[] = new ArrayWrapper([
                    EntityConstants::CUSTOMER_ADDRESS_ID => $cartItem->get(EntityConstants::CUSTOMER_ADDRESS_ID, $customerAddressId),
                    EntityConstants::SOURCE_ADDRESS_KEY => $cartItem->get(EntityConstants::SOURCE_ADDRESS_KEY, 'main')
                ]);

            } else {

                // we can assume the address for the cart item is not changing
                //  but we need to recollect in case qty or weight changed

                $recollectShipping[] = new ArrayWrapper([
                    EntityConstants::CUSTOMER_ADDRESS_ID => $cartItem->get(EntityConstants::CUSTOMER_ADDRESS_ID, 'main'),
                    EntityConstants::SOURCE_ADDRESS_KEY => $cartItem->get(EntityConstants::SOURCE_ADDRESS_KEY, 'main')
                ]);
            }

        } else {

            // always recollect main address when multi shipping is disabled

            $recollectShipping[] = new ArrayWrapper([
                EntityConstants::CUSTOMER_ADDRESS_ID => $cartItem->get(EntityConstants::CUSTOMER_ADDRESS_ID, 'main'),
                EntityConstants::SOURCE_ADDRESS_KEY => $cartItem->get(EntityConstants::SOURCE_ADDRESS_KEY, 'main')
            ]);
        }

        return $this;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCartAddProduct(CoreEvent $event)
    {
        // parse/convert API requests
        switch($event->getContentType()) {
            case CoreEvent::JSON:

                $apiRequest = $event->getApiRequest()
                    ? $event->getApiRequest()
                    : @ (array) json_decode($event->getRequest()->getContent());

                if (isset($apiRequest['qty'])
                    && (isset($apiRequest['sku']) || isset($apiRequest['product_id']))
                ) {

                    $keys = ['cart_id','qty','is_add','product_id','sku','simple_id','simple_sku'];
                    foreach($apiRequest as $key => $value) {

                        if (!in_array($key, $keys)) {
                            continue;
                        }

                        $event->getRequest()->request->set($key, $value);
                    }
                }

                break;
            default:

                break;
        }

        /**
         * Strategy:
         *
         * 1. Get keys from Request or Event
         * 2. Determine Product Type eg Simple, Configurable
         * 3. Check if the sku is already in the cart
         * 4. Handle Product Type logic
         *
         * Simple:
         * 1. Ensure it exists
         *
         * Configurable:
         * 1. Ensure both child and parent exist
         * 2. Ensure they are related
         *
         */

        $request = $event->getRequest();
        $this->initCart($request);

        $key = $request->get('sku', '') || $event->get('sku')
            ? 'sku'
            : 'product_id';

        $value = $event->get($key)
            ? $event->get($key)
            : $request->get($key, '');

        $qty = is_numeric($event->get('qty', ''))
            ? (int) $event->get('qty')
            : (int) $request->get('qty', 1);

        $this->setQty($qty);
        $this->setIsAdd((bool) $event->get('is_add'));

        /** @var \MobileCart\CoreBundle\Entity\Product $product */
        $product = $this->loadProduct($key, $value);
        if (!$product
            || !$product->getIsEnabled()
        ) {
            $event->addErrorMessage('Product not found');
            $event->setResponseCode(404);
            return;
        }

        $recollectShipping = $event->get('recollect_shipping', []); // r = [object, object] , object:{EntityConstants::CUSTOMER_ADDRESS_ID:'',EntityConstants::SOURCE_ADDRESS_KEY:''}
        switch($product->getType()) {
            case EntityConstants::PRODUCT_TYPE_CONFIGURABLE:

                $event->set('product_type', $product->getType());

                $simpleKey = $request->get('simple_sku') || $event->get('simple_sku')
                    ? 'simple_sku'
                    : 'simple_id';

                $simpleValue = $event->get($simpleKey)
                    ? $event->get($simpleKey)
                    : $request->get($simpleKey, '');

                $lookupKey = $simpleKey == 'simple_sku'
                    ? 'sku'
                    : 'product_id';

                $item = $this->getCartService()->findItem($lookupKey, $simpleValue);
                if ($item) {

                    $totalQty = $this->getIsAdd()
                        ? $item->getQty() + $this->getQty()
                        : $this->getQty();

                    if ($this->meetsCriteria($item, $totalQty, $event)) {

                        // update quantity
                        $this->getCartService()
                            ->setProductQty($item->getProductId(), $totalQty)
                            ->updateItemEntityQty($item->getProductId(), $totalQty);

                        // update tier price
                        $this->updateTierPrice($item);
                        $event->set('item', $item); // pass the current item to the next event listener
                        $this->collectAddresses($event, $item, $recollectShipping);
                        $this->setSuccess(true);
                    }
                } else {

                    if (!$product->getIsPublic()) {
                        $event->addErrorMessage('Product not found');
                        $event->setResponseCode(404);
                        return;
                    }

                    $simpleProduct = $this->loadProduct($lookupKey, $simpleValue);
                    if ($simpleProduct) {

                        $parentOptions = $simpleProduct
                            ? [
                                'id' => $product->getId(),
                                'sku' => $product->getSku(),
                                'slug' => $product->getSlug(),
                            ] : [];

                        $item = $this->getCartService()->convertProductToItem($simpleProduct, $parentOptions, $this->getQty());
                        if ($this->meetsCriteria($item, $this->getQty(), $event)) {

                            // update tier price
                            $this->updateTierPrice($item);

                            $this->getCartService()->addItem($item, $this->getQty());
                            $itemEntity = $this->getCartService()->convertItemToEntity($item);
                            $this->getCartService()->addNewCartItemEntity($itemEntity);
                            $event->set('item', $item); // pass the current item to the next event listener
                            $this->collectAddresses($event, $item, $recollectShipping);
                            $this->setSuccess(true);
                        }
                    }
                }

                break;
            case EntityConstants::PRODUCT_TYPE_SIMPLE:
                $event->set('product_type', $product->getType());
                $item = $this->getCartService()->findItem('product_id', $product->getId());
                if ($item) {

                    $totalQty = $this->getIsAdd()
                        ? $item->getQty() + $this->getQty()
                        : $this->getQty();

                    if ($this->meetsCriteria($item, $totalQty, $event)) {

                        // update tier price
                        $this->updateTierPrice($item);

                        // update quantity
                        $this->getCartService()
                            ->setProductQty($product->getId(), $totalQty)
                            ->updateItemEntityQty($product->getId(), $totalQty);

                        $event->set('item', $item); // pass the current item to the next event listener
                        $this->collectAddresses($event, $item, $recollectShipping);
                        $this->setSuccess(true);
                    }
                } else {

                    if (!$product->getIsPublic()) {
                        $event->addErrorMessage('Product not found');
                        $event->setResponseCode(404);
                        return;
                    }

                    $item = $this->getCartService()->convertProductToItem($product, [], $this->getQty());
                    if ($this->meetsCriteria($item, $this->getQty(), $event)) {

                        // update tier price
                        $this->updateTierPrice($item);

                        $this->getCartService()->addItem($item, $this->getQty());
                        $event->set('item', $item); // pass the current item to the next event listener

                        $itemEntity = $this->getCartService()->convertItemToEntity($item);
                        $this->getCartService()->addNewCartItemEntity($itemEntity);

                        $this->collectAddresses($event, $item, $recollectShipping);
                        $this->setSuccess(true);
                    }
                }

                break;
            default:

                break;
        }

        $event->set('recollect_shipping', $recollectShipping) // this is handled in UpdateTotalsShipping
            ->setSuccess((bool) $this->getSuccess());

        if ($this->getSuccess()
            && !$event->getIsMassUpdate()
        ) {
            $event->addSuccessMessage('Product Added to Cart : ' . $product->getSku());
        }
    }
}
