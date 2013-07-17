<?php

namespace Oro\Bundle\NotificationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class EventRepository extends EntityRepository
{
    public function getEventNames()
    {
        return $this->createQueryBuilder('e')
            ->select('e.name')
            ->getQuery()
            ->getResult();
    }
}
