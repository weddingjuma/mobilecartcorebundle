<?php

namespace MobileCart\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ShippingMethodRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ShippingMethodRepository
    extends EntityRepository
    implements CartRepositoryInterface
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
                CartRepositoryInterface::CODE  => 'id',
                CartRepositoryInterface::LABEL => 'ID',
                CartRepositoryInterface::DATATYPE =>  'number',
            ],
            [
                CartRepositoryInterface::CODE => 'company',
                CartRepositoryInterface::LABEL => 'Company',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE => 'method',
                CartRepositoryInterface::LABEL => 'Method',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
        ];
    }

    /**
     * @return bool
     */
    public function isEAV()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function hasImages()
    {
        return false;
    }

    /**
     * @return mixed|string
     */
    public function getSearchField()
    {
        return 'method';
    }

    /**
     * @return int|mixed
     */
    public function getSearchMethod()
    {
        return self::SEARCH_METHOD_LIKE;
    }
}
