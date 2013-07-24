<?php

namespace UniHalle\RentBundle\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Fresh\Bundle\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class BookingExtensionStatusType extends AbstractEnumType
{
    const PRELIMINARY = 'preliminary';
    const CANCELED    = 'canceled';
    const APPROVED    = 'approved';

    protected $name = 'BookingExtensionStatusType';

    protected static $choices = [
        self::PRELIMINARY => 'Vorläufige Verlängerung',
        self::CANCELED    => 'Abgelehnte Verlängerung',
        self::APPROVED    => 'Genehmigte Verlängerung'
    ];
}
