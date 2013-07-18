<?php

namespace UniHalle\RentBundle\Helper;

class BookingHelper
{
    private $em;

    public function __construct($em = null)
    {
        $this->em = $em;
        if (is_null($this->em)) {
            // just an ugly hack
            global $kernel;
            if ('AppCache' == get_class($kernel)) {
                $kernel = $kernel->getKernel();
            }
            $this->em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        }
    }

    /**
     * Calculates the date until a device is blocked
     *
     * @param \DateTime $endDate
     * @return \DatemTime
     */
    public function calcBookedUntil($endDate)
    {
        $holidays = $this->em->getRepository('RentBundle:Configuration')->getHolidays();
        $blockingPeriod = (int)$this->em->getRepository('RentBundle:Configuration')->getValue('blockingPeriod');
        $dateBlockedUntil = clone $endDate;

        if ($blockingPeriod == 0) {
            return $dateBlockedUntil;
        }

        while ($blockingPeriod > 1) {
            $dateBlockedUntil->add(new \DateInterval('P1D'));

            if ($dateBlockedUntil->format('N') >= 6) {
                continue;
            }
            if (in_array($dateBlockedUntil, $holidays)) {
                continue;
            }

            $blockingPeriod--;
        }

        return $dateBlockedUntil;
    }
}
