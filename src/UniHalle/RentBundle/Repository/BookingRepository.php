<?php

namespace UniHalle\RentBundle\Repository;

use Doctrine\ORM\EntityRepository;
use UniHalle\RentBundle\Entity\Booking;
use UniHalle\RentBundle\Types\BookingStatusType;

class BookingRepository extends EntityRepository
{
    public function getCurrentBookings($deviceId, $blockingPeriod)
    {
        $blockingInterval = new \DateInterval('P' . $blockingPeriod . 'D');
        $blockingInterval->invert = 1;
        $startDate = new \DateTime('first day of this month 00:00:00');
        $startDate->add($blockingInterval);

        $query = $this->getEntityManager()
                      ->createQuery(
                          'SELECT b FROM RentBundle:Booking b
                           LEFT JOIN b.device d
                           WHERE d.id = :device_id
                           AND (b.dateFrom >= :start_date OR b.dateTo >= :start_date)
                           AND b.status!=:status
                           ORDER BY b.dateFrom'
                      )
                      ->setParameter('device_id', $deviceId)
                      ->setParameter('start_date', $startDate->format('Y-m-d'))
                      ->setParameter('status', BookingStatusType::CANCELED);
        return $query->getResult();
    }
}
