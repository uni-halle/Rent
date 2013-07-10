<?php

namespace UniHalle\RentBundle\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Fresh\Bundle\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class BookingStatusType extends AbstractEnumType
{
    const PRELIMINARY = 'preliminary';
    const CANCELED    = 'canceled';
    const APPROVED    = 'approved';
    const IN_RENT     = 'inRent';
    const GOT_BACK    = 'gotBack';

    protected $name = 'BookingStatusType';

    protected static $choices = [
        self::PRELIMINARY => 'Vorläufige Buchung',
        self::CANCELED    => 'Abgelehnte Buchung',
        self::APPROVED    => 'Genehmigte Buchung',
        self::IN_RENT     => 'Gerät ausgeliehen',
        self::GOT_BACK    => 'Gerät zurück erhalten',
    ];
}
