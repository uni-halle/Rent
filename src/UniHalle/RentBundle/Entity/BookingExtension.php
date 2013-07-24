<?php

namespace UniHalle\RentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Fresh\Bundle\DoctrineEnumBundle\Validator\Constraints as DoctrineAssert;
use UniHalle\RentBundle\Types\BookingExtensionStatusType;
use UniHalle\RentBundle\Helper\BookingHelper;

/**
 * @ORM\Entity(repositoryClass="UniHalle\RentBundle\Repository\BookingExtensionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class BookingExtension
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Booking", inversedBy="extensions")
     * @Assert\NotNull
     */
    private $booking;

    /**
     * @ORM\Column(type="date")
     */
    private $dateTo;

    /**
     * @ORM\Column(type="date")
     */
    private $dateBlockedUntil;

    /**
     * @DoctrineAssert\Enum(entity="UniHalle\RentBundle\Types\BookingExtensionStatusType")
     * @ORM\Column(type="string", type="BookingExtensionStatusType", nullable=false)
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
     * Set dateTo
     *
     * @param \DateTime $dateTo
     * @return BookingExtension
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
     * @return BookingExtension
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
     * Set status
     *
     * @param string $status
     * @return BookingExtension
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
     * Set booking
     *
     * @param \UniHalle\RentBundle\Entity\Extension $booking
     * @return BookingExtension
     */
    public function setBooking(\UniHalle\RentBundle\Entity\Booking $booking = null)
    {
        $this->booking = $booking;

        return $this;
    }

    /**
     * Get booking
     *
     * @return \UniHalle\RentBundle\Entity\Booking
     */
    public function getBooking()
    {
        return $this->booking;
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
