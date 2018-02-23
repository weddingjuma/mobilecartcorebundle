<?php

namespace MobileCart\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CustomerAddressRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CustomerAddressRepository
    extends EntityRepository
    implements CartRepositoryInterface
{
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
     * @return array
     */
    public function getSortableFields()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'company' => 'Company',
            'city' => 'City',
            'region' => 'Region/State',
        ];
    }

    /**
     * @return array
     */
    public function getFilterableFields()
    {
        return [
            [
                CartRepositoryInterface::CODE  => 'customer_id',
                CartRepositoryInterface::LABEL => 'Customer',
                CartRepositoryInterface::DATATYPE =>  'number',
            ],
            [
                CartRepositoryInterface::CODE  => 'name',
                CartRepositoryInterface::LABEL => 'Name',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'company',
                CartRepositoryInterface::LABEL => 'Company',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'id',
                CartRepositoryInterface::LABEL => 'ID',
                CartRepositoryInterface::DATATYPE =>  'number',
            ],
            [
                CartRepositoryInterface::CODE  => 'city',
                CartRepositoryInterface::LABEL => 'City',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'region',
                CartRepositoryInterface::LABEL => 'Region/State',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'country_id',
                CartRepositoryInterface::LABEL => 'Country',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
        ];
    }

    /**
     * @return mixed|string
     */
    public function getSearchField()
    {
        return ['name', 'company'];
    }

    /**
     * @return int|mixed
     */
    public function getSearchMethod()
    {
        return self::SEARCH_METHOD_LIKE;
    }
}
