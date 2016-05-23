<?php

namespace MobileCart\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Entity\ContentSlot;

/**
 * ContentSlotRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ContentSlotRepository
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
            'parent' => 'Content ID',
            'title' => 'Title',
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
        return 'title';
    }

    /**
     * @return int|mixed
     */
    public function getSearchMethod()
    {
        return self::SEARCH_METHOD_LIKE;
    }


}
