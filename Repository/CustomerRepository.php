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
            [
                'code'  => 'name',
                'label' => 'Name',
                'type'  => 'string',
            ],
            [
                'code'  => 'created_at',
                'label' => 'Created At',
                'type'  => 'date',
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
