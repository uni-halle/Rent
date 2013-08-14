<?php

namespace UniHalle\RentBundle\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Fresh\Bundle\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class PersonType extends AbstractEnumType
{
    const EMPLOYEE = 'employee';
    const STUDENT  = 'student';
    const GUEST    = 'guest';
    const UNKNOWN  = 'unknown';

    protected $name = 'PersonType';

    protected static $choices = [
        self::EMPLOYEE => 'Mitarbeiter',
        self::STUDENT  => 'Student',
        self::GUEST    => 'Gast',
        self::UNKNOWN  => 'unbekannt',
    ];


    public static function mapMluPersonType($mluPersonType)
    {
        switch ($mluPersonType) {
            case 1:
                return self::EMPLOYEE;
                break;
            case 2:
                return self::STUDENT;
                break;
            case 3:
                return self::GUEST;
                break;
            default:
                return self::UNKNOWN;
                break;
        }
    }

    public static function getAccountTypeName($type)
    {
        return self::$choices[$type];
    }
}
