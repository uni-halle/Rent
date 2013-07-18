<?php

namespace UniHalle\RentBundle\Repository;

use Doctrine\ORM\EntityRepository;
use UniHalle\RentBundle\Entity\Booking;
use UniHalle\RentBundle\Helper\BookingHelper;
use UniHalle\RentBundle\Types\BookingStatusType;

class BookingRepository extends EntityRepository
{
    public function getCurrentBookings($deviceId)
    {
        $blockingPeriod = (int)$this->getEntityManager()->getRepository('RentBundle:Configuration')->getValue('blockingPeriod');

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

    public function bookingExistsInPeriod($deviceId, $startDate, $endDate, $bookingToExclude = null)
    {
        $bookingHelper = new BookingHelper($this->getEntityManager());
        $endDate = $bookingHelper->calcBookedUntil($endDate);

        $query = $this->getEntityManager()
                      ->createQuery(
                          'SELECT COUNT(b) FROM RentBundle:Booking b
                           LEFT JOIN b.device d
                           WHERE d.id = :device_id
                           AND (
                               (b.dateFrom >= :start_date AND b.dateFrom <= :end_date) OR
                               (b.dateBlockedUntil >= :start_date AND b.dateBlockedUntil <= :end_date)
                           )
                           AND b.status!=:status
                           AND b.id!=:bookingToExclude'
                      )
                      ->setParameter('device_id', $deviceId)
                      ->setParameter('start_date', $startDate->format('Y-m-d'))
                      ->setParameter('end_date', $endDate->format('Y-m-d'))
                      ->setParameter('status', BookingStatusType::CANCELED)
                      ->setParameter('bookingToExclude', (!is_null($bookingToExclude)) ? $bookingToExclude->getId() : 0);

        return ($query->getSingleScalarResult() > 0);
    }
}
