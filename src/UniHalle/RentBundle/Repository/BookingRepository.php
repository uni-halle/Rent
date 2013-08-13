<?php

namespace UniHalle\RentBundle\Repository;

use Doctrine\ORM\EntityRepository;
use UniHalle\RentBundle\Entity\Booking;
use UniHalle\RentBundle\Helper\BookingHelper;
use UniHalle\RentBundle\Types\BookingStatusType;

class BookingRepository extends EntityRepository
{
    public function getCurrentBookings($deviceId, $bookingToExlude = null)
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
                           AND (b.dateFrom >= :start_date OR b.dateTo >= :start_date OR (b.extensionDateTo IS NOT NULL AND b.extensionDateTo >= :start_date))
                           AND b.status!=:status
                           AND b.id!=:bookingToExclude
                           ORDER BY b.dateFrom'
                      )
                      ->setParameter('device_id', $deviceId)
                      ->setParameter('start_date', $startDate->format('Y-m-d'))
                      ->setParameter('status', BookingStatusType::CANCELED)
                      ->setParameter('bookingToExclude', (!is_null($bookingToExlude)) ? $bookingToExlude->getId() : 0);

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
                               (b.dateBlockedUntil >= :start_date AND b.dateBlockedUntil <= :end_date) OR
                               (b.extensionDateBlockedUntil IS NOT NULL AND b.extensionDateBlockedUntil >= :start_date AND b.extensionDateBlockedUntil <= :end_date)
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

    public function getBookings($userId, $deviceId, $time)
    {
        $userQuery = ($userId != 0) ? ' AND b.user='.(int)$userId : '';
        $deviceQuery = ($deviceId != 0) ? ' AND b.device='.(int)$deviceId : '';

        if ($time == 'now') {
            $query = $this->getEntityManager()
                          ->createQuery(
                              'SELECT b FROM RentBundle:Booking b
                               LEFT JOIN b.device d
                               LEFT JOIN b.user u
                               WHERE
                                   (
                                     b.dateFrom>=CURRENT_DATE() OR
                                     (b.dateFrom<=CURRENT_DATE() AND b.dateTo>=CURRENT_DATE()) OR
                                     (b.dateFrom<=CURRENT_DATE() AND b.extensionDateTo IS NOT NULL AND b.extensionDateTo>=CURRENT_DATE())
                                   )
                                   '.$userQuery.'
                                   '.$deviceQuery.'
                               ORDER BY b.dateFrom'
                          );
        } else if ($time == 'past') {
            $query = $this->getEntityManager()
                          ->createQuery(
                              'SELECT b FROM RentBundle:Booking b
                               LEFT JOIN b.device d
                               LEFT JOIN b.user u
                               WHERE
                                   (b.dateTo<CURRENT_DATE() OR (b.extensionDateTo IS NOT NULL AND b.extensionDateTo<CURRENT_DATE()))
                                   '.$userQuery.'
                                   '.$deviceQuery.'
                               ORDER BY b.dateFrom'
                          );
        }
        return $query;
    }
}
