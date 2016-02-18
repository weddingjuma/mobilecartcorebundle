<?php

namespace MobileCart\CoreBundle\Payment;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;

class CollectPaymentMethodRequest extends ArrayWrapper
{
    public function __construct()
    {
        parent::__construct([
            'include_all' => 0,
            'to_array'    => 0,
            'postcode'    => '',
            'country_id'  => '',
            'region'      => '',
        ]);
    }
}
