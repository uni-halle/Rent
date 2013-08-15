<?php

namespace UniHalle\RentBundle\Repository;

use Doctrine\ORM\EntityRepository;
use UniHalle\RentBundle\Entity\User;
use UniHalle\RentBundle\Types\BookingStatusType;

class UserRepository extends EntityRepository
{
    public function getUsers($status)
    {
        $query = $this->getEntityManager()
                      ->createQuery(
                          'SELECT u FROM RentBundle:User u
                           WHERE u.status=:status
                           ORDER BY u.username'
                      )
                      ->setParameter('status', $status);
        return $query;
    }
}
