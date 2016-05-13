<?php

namespace MobileCart\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ItemVarSetRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ItemVarOptionIntRepository
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
            'item_var_id' => 'Variant',
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
                'code'  => 'id',
                'label' => 'ID',
                'type'  => 'number',
            ),
            array(
                'code' => 'item_var_id',
                'label' => 'Variant',
                'type' => 'number',
            ),
            array(
                'code'  => 'value',
                'label' => 'Value',
                'type'  => 'string',
            ),
            array(
                'code'  => 'is_in_stock',
                'label' => 'Is In Stock',
                'type'  => 'boolean',
            ),
            array(
                'code'  => 'additional_price',
                'label' => 'Addt\'l Price',
                'type'  => 'number',
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
