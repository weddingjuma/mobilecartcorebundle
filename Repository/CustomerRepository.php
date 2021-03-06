<?php

namespace MobileCart\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CustomerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CustomerRepository
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
            'email' => 'Email',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'created_at' => 'Created At',
            'name' => 'Name',
        ];
    }

    /**
     * @return array
     */
    public function getFilterableFields()
    {
        return [
            [
                CartRepositoryInterface::CODE  => 'email',
                CartRepositoryInterface::LABEL => 'Email',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'first_name',
                CartRepositoryInterface::LABEL => 'First Name',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'last_name',
                CartRepositoryInterface::LABEL => 'Last Name',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'id',
                CartRepositoryInterface::LABEL => 'ID',
                CartRepositoryInterface::DATATYPE =>  'number',
            ],
            [
                CartRepositoryInterface::CODE  => 'name',
                CartRepositoryInterface::LABEL => 'Name',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'created_at',
                CartRepositoryInterface::LABEL => 'Created At',
                CartRepositoryInterface::DATATYPE =>  'date',
            ],
        ];
    }

    /**
     * @return mixed|string
     */
    public function getSearchField()
    {
        return ['email', 'first_name', 'last_name'];
    }

    /**
     * @return int|mixed
     */
    public function getSearchMethod()
    {
        return self::SEARCH_METHOD_LIKE;
    }
}
