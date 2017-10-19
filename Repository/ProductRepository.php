<?php

namespace MobileCart\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ProductRepository
 * @package MobileCart\CoreBundle\Repository
 */
class ProductRepository
    extends EntityRepository
    implements CartRepositoryInterface, AdvSortableInterface
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
        return true;
    }

    /**
     * @return array
     */
    public function getSortableFields()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'page_title' => 'Page Title',
            'name' => 'Name',
            'slug' => 'Slug',
            'sort_order' => 'Sort Order',
            'type' => 'Product Type',
            'sku' => 'SKU',
            'price' => 'Price',
            'special_price' => 'Special Price',
            'qty' => 'Qty',
            'is_in_stock' => 'In Stock',
            'is_taxable' => 'Taxable',
        ];
    }

    /**
     * @return array
     */
    public function getAdvSortableFields()
    {
        return [
            'sort_order' => [
                'label' => 'Sort Order',
                'active' => 0,
                'value' => 'sort_order',
                'dir' => 'asc',
            ],
            'price_asc' => [
                'label' => 'Lowest Price',
                'active' => 0,
                'value' => 'price',
                'dir' => 'asc',
            ],
            'price_desc' => [
                'label' => 'Highest Price',
                'active' => 0,
                'value' => 'price',
                'dir' => 'desc',
            ],
            'created_at_newest' => [
                'label' => 'Newest',
                'active' => 0,
                'value' => 'created_at',
                'dir' => 'desc',
            ],
            'name_az' => [
                'label' => 'Name (A-Z)',
                'active' => 0,
                'value' => 'name',
                'dir' => 'asc',
            ],
            'name_za' => [
                'label' => 'Name (Z-A)',
                'active' => 0,
                'value' => 'name',
                'dir' => 'desc',
            ],
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
                'code'  => 'name',
                'label' => 'Name',
                'type'  => 'string',
            ],
            [
                'code'  => 'category_id',
                'label' => 'Category',
                'type'  => 'number',
                'join' => [
                    'type' => 'left', // left, inner, etc
                    'table' => EntityConstants::CATEGORY_PRODUCT,
                    'column' => 'product_id', // eg product_id , without the prefix
                    'join_alias' => 'main', // main table alias
                    'join_column' => 'id', // eg id, item_var_set_id, etc
                ]
            ],
            [
                'code'  => 'created_at',
                'label' => 'Created At',
                'type'  => 'date',
            ],
            [
                'code'  => 'sort_order',
                'label' => 'Sort Order',
                'type'  => 'number',
            ],
            [
                'code'  => 'page_title',
                'label' => 'Page Title',
                'type'  => 'string',
            ],
            [
                'code'  => 'slug',
                'label' => 'Slug',
                'type'  => 'string',
            ],
            [
                'code'  => 'type',
                'label' => 'Product Type',
                'type'  => 'number',
                'choices' => [
                    [
                        'value' => 1,
                        'label' => 'Simple'
                    ],
                    [
                        'value' => 2,
                        'label' => 'Configurable',
                    ],
                ],
            ],
            [
                'code'  => 'sku',
                'label' => 'SKU',
                'type'  => 'string',
            ],
            [
                'code'  => 'price',
                'label' => 'Price',
                'type'  => 'number',
            ],
            [
                'code'  => 'special_price',
                'label' => 'Special Price',
                'type'  => 'number',
            ],
            [
                'code'  => 'qty',
                'label' => 'Qty',
                'type'  => 'number',
            ],
            [
                'code'  => 'is_in_stock',
                'label' => 'In Stock',
                'type'  => 'boolean',
                'choices' => [
                    [
                        'value' => 0,
                        'label' => 'No',
                    ],
                    [
                        'value' => 1,
                        'label' => 'Yes',
                    ],
                ],
            ],
            [
                'code'  => 'is_enabled',
                'label' => 'Enabled',
                'type'  => 'boolean',
                'choices' => [
                    [
                        'value' => 0,
                        'label' => 'No',
                    ],
                    [
                        'value' => 1,
                        'label' => 'Yes',
                    ],
                ],
            ],
            [
                'code'  => 'is_public',
                'label' => 'Public',
                'type'  => 'boolean',
                'choices' => [
                    [
                        'value' => 0,
                        'label' => 'No',
                    ],
                    [
                        'value' => 1,
                        'label' => 'Yes',
                    ],
                ],
            ],
            [
                'code'  => 'is_taxable',
                'label' => 'Taxable',
                'type'  => 'boolean',
                'choices' => [
                    [
                        'value' => 0,
                        'label' => 'No',
                    ],
                    [
                        'value' => 1,
                        'label' => 'Yes',
                    ],
                ],
            ],
        ];

    }

    /**
     * @return array|string
     */
    public function getSearchField()
    {
        return ['name', 'sku', 'fulltext_search'];
    }

    /**
     * @return int|mixed
     */
    public function getSearchMethod()
    {
        return self::SEARCH_METHOD_FULLTEXT;
    }
}
