<?php

namespace UniHalle\RentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Fresh\Bundle\DoctrineEnumBundle\Validator\Constraints as DoctrineAssert;
use UniHalle\RentBundle\Types\BookingStatusType;

/**
 * @ORM\Entity(repositoryClass="UniHalle\RentBundle\Repository\BookingRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Booking
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Device", inversedBy="bookings")
     * @Assert\NotNull
     */
    private $device;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="bookings")
     * @Assert\NotNull
     */
    private $user;

    /**
     * @ORM\Column(type="date")
     */
    private $dateFrom;

    /**
     * @ORM\Column(type="date")
     */
    private $dateTo;

    /**
     * @DoctrineAssert\Enum(entity="UniHalle\RentBundle\Types\BookingStatusType")
     * @ORM\Column(type="string", type="BookingStatusType", nullable=false)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updated;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateFrom
     *
     * @param \DateTime $dateFrom
     * @return Booking
     */
    public function setDateFrom($dateFrom)
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    /**
     * Get dateFrom
     *
     * @return \DateTime
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * Set dateTo
     *
     * @param \DateTime $dateTo
     * @return Booking
     */
    public function setDateTo($dateTo)
    {
        $this->dateTo = $dateTo;

        return $this;
    }

    /**
     * Get dateTo
     *
     * @return \DateTime
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Booking
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get date after return and blocking period
     * skip weekends and holidays
     *
     * @todo: check holidays
     * @return \DateTime
     */
    public function getDateNextAvailable($blockingPeriod) {
        $nextAvailable = clone $this->getDateTo();
        while ($blockingPeriod > 0) {
            if ($nextAvailable->format('N') >= 6) {
                $nextAvailable->add(new \DateInterval('P2D'));
                continue;
            }
            $nextAvailable->add(new \DateInterval('P1D'));
            $blockingPeriod--;
        }
        return $nextAvailable;
    }

    /**
     * Set device
     *
     * @param \UniHalle\RentBundle\Entity\Device $device
     * @return Booking
     */
    public function setDevice(\UniHalle\RentBundle\Entity\Device $device = null)
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Get device
     *
     * @return \UniHalle\RentBundle\Entity\Device
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Set user
     *
     * @param \UniHalle\RentBundle\Entity\User $user
     * @return Booking
     */
    public function setUser(\UniHalle\RentBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \UniHalle\RentBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedValue()
    {
        $this->updated = new \DateTime();
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        $this->created = new \DateTime();
    }
}
