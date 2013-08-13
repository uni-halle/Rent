<?php

namespace UniHalle\RentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Fresh\Bundle\DoctrineEnumBundle\Validator\Constraints as DoctrineAssert;
use UniHalle\RentBundle\Types\BookingStatusType;
use UniHalle\RentBundle\Helper\BookingHelper;
use UniHalle\RentBundle\Entity\BookingExtension;

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
     * @ORM\Column(type="date", nullable=true)
     */
    private $extensionDateTo;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateBlockedUntil;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $extensionDateBlockedUntil;

    /**
     * @DoctrineAssert\Enum(entity="UniHalle\RentBundle\Types\BookingStatusType")
     * @ORM\Column(type="string", type="BookingStatusType", nullable=false)
     */
    private $status;

    /**
     * @DoctrineAssert\Enum(entity="UniHalle\RentBundle\Types\BookingStatusType")
     * @ORM\Column(type="string", type="BookingStatusType", nullable=true)
     */
    private $extensionStatus;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updated;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    public function __construct()
    {
        $this->extensions = new ArrayCollection();
    }

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

        $bookingHelper = new BookingHelper();
        $this->setDateBlockedUntil($bookingHelper->calcBookedUntil($dateTo));

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
     * Set dateBlockedUntil
     *
     * @param \DateTime $dateBlockedUntil
     * @return Booking
     */
    public function setDateBlockedUntil($dateBlockedUntil)
    {
        $this->dateBlockedUntil = $dateBlockedUntil;

        return $this;
    }

    /**
     * Get dateBlockedUntil
     *
     * @return \DateTime
     */
    public function getDateBlockedUntil()
    {
        return $this->dateBlockedUntil;
    }

    /**
     * Set extensionDateTo
     *
     * @param \DateTime $extensionDateTo
     * @return Booking
     */
    public function setExtensionDateTo($extensionDateTo)
    {
        $this->extensionDateTo = $extensionDateTo;
        if ($extensionDateTo === null) {
            $this->setExtensionDateBlockedUntil(null);
        } else {
            $bookingHelper = new BookingHelper();
            $this->setExtensionDateBlockedUntil($bookingHelper->calcBookedUntil($extensionDateTo));
        }

        return $this;
    }

    /**
     * Get extensionDateTo
     *
     * @return \DateTime
     */
    public function getExtensionDateTo()
    {
        return $this->extensionDateTo;
    }

    /**
     * Set extensionDateBlockedUntil
     *
     * @param \DateTime $extensionDateBlockedUntil
     * @return Booking
     */
    public function setExtensionDateBlockedUntil($extensionDateBlockedUntil)
    {
        $this->extensionDateBlockedUntil = $extensionDateBlockedUntil;

        return $this;
    }

    /**
     * Get extensionDateBlockedUntil
     *
     * @return \DateTime
     */
    public function getExtensionDateBlockedUntil()
    {
        return $this->extensionDateBlockedUntil;
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
     * Set extensionStatus
     *
     * @param string $extensionStatus
     * @return Booking
     */
    public function setExtensionStatus($status)
    {
        $this->extensionStatus = $status;

        return $this;
    }

    /**
     * Get extensionStatus
     *
     * @return status
     */
    public function getExtensionStatus()
    {
        return $this->extensionStatus;
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
