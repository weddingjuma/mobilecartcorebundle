<?php

namespace MobileCart\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ItemVarSetRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ItemVarOptionDatetimeRepository
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
            'item_var_id' => 'Custom Field',
            'item_var_name' => 'Custom Field Name',
            'value' => 'Value',
            'is_in_stock' => 'Is In Stock',
            'additional_price' => 'Addt\'l Price'
        ];
    }

    /**
     * @return array
     */
    public function getFilterableFields()
    {
        return array(
            array(
                CartRepositoryInterface::CODE  => 'id',
                CartRepositoryInterface::LABEL => 'ID',
                CartRepositoryInterface::DATATYPE =>  'number',
            ),
            array(
                CartRepositoryInterface::CODE => 'item_var_id',
                CartRepositoryInterface::LABEL => 'Custom Field',
                CartRepositoryInterface::DATATYPE =>  'number',
            ),
            array(
                CartRepositoryInterface::CODE  => 'value',
                CartRepositoryInterface::LABEL => 'Value',
                CartRepositoryInterface::DATATYPE =>  'string',
            ),
            array(
                CartRepositoryInterface::CODE  => 'is_in_stock',
                CartRepositoryInterface::LABEL => 'Is In Stock',
                CartRepositoryInterface::DATATYPE =>  'boolean',
            ),
            array(
                CartRepositoryInterface::CODE  => 'additional_price',
                CartRepositoryInterface::LABEL => 'Addt\'l Price',
                CartRepositoryInterface::DATATYPE =>  'number',
            ),
        );
    }

    /**
     * @return mixed|string
     */
    public function getSearchField()
    {
        return 'value';
    }

    /**
     * @return int|mixed
     */
    public function getSearchMethod()
    {
        return self::SEARCH_METHOD_LIKE;
    }
}
