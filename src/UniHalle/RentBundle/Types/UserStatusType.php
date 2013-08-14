<?php

namespace UniHalle\RentBundle\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Fresh\Bundle\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class UserStatusType extends AbstractEnumType
{
    const WAITING  = 'waiting';
    const ACTIVE   = 'active';
    const DISABLED = 'disabled';

    protected $name = 'UserStatusType';

    protected static $choices = [
        self::WAITING  => 'Auf Freischaltung wartend',
        self::ACTIVE   => 'Aktiv',
        self::DISABLED => 'Gesperrt',
    ];
}
