<?php

namespace MobileCart\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * RefCountryRegionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RefCountryRegionRepository extends EntityRepository
{
    /**
     * @return bool
     */
    public function hasImages()
    {
        return false;
    }
}
