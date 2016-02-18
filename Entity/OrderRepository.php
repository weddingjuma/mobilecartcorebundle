<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * OrderRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class OrderRepository
    extends EntityRepository
    implements CartRepositoryInterface
{

    /**
     * @return bool
     */
    public function isEAV()
    {
        return true;
    }

    /**
     * @return array
     */
    public function getSortableFields()
    {
        return [
            'total' => 'Total',
        ];
    }

    /**
     * @return array
     */
    public function getFilterableFields()
    {
        return [
            [
                'code'  => 'total',
                'label' => 'Total',
                'type'  => 'number',
            ],
        ];
    }

    /**
     * @return mixed|string
     */
    public function getSearchField()
    {
        return 'billing_name';
    }

    /**
     * @return int|mixed
     */
    public function getSearchMethod()
    {
        return self::SEARCH_METHOD_LIKE;
    }
}
