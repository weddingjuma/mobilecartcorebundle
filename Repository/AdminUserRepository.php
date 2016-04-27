<?php

namespace MobileCart\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * AdminUserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AdminUserRepository
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
     * @return array
     */
    public function getSortableFields()
    {
        return [
            'id' => 'ID',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
        ];
    }

    /**
     * @return array
     */
    public function getFilterableFields()
    {
        return [
            [
                'code'  => 'first_name',
                'label' => 'First Name',
                'type'  => 'string',
            ],
            [
                'code'  => 'last_name',
                'label' => 'Last Name',
                'type'  => 'string',
            ],
            [
                'code'  => 'id',
                'label' => 'ID',
                'type'  => 'number',
            ],
        ];
    }

    /**
     * @return mixed|string
     */
    public function getSearchField()
    {
        return 'last_name';
    }

    /**
     * @return int|mixed
     */
    public function getSearchMethod()
    {
        return self::SEARCH_METHOD_LIKE;
    }
}