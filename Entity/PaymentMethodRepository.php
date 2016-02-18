<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * PaymentMethodRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PaymentMethodRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function getSortableFields()
    {
        return [
            'id' => 'ID',
            'company' => 'Company',
            'method' => 'Method',
        ];
    }

    /**
     * @return array
     */
    public function getFilterableFields()
    {
        return [
            [
                'code'  => 'id',
                'label' => 'ID',
                'type'  => 'number',
            ],
            [
                'code' => 'company',
                'label' => 'Company',
                'type' => 'string',
            ],
            [
                'code' => 'method',
                'label' => 'Method',
                'type' => 'string',
            ],
        ];
    }
}
