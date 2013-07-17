<?php

namespace UniHalle\RentBundle\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Fresh\Bundle\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class SiteType extends AbstractEnumType
{
    const CONTENT    = 'content';
    const USER_MAIL  = 'user_mail';
    const ADMIN_MAIL = 'admin_mail';
    const DOCUMENT   = 'document';

    protected $name = 'SiteType';

    protected static $choices = [
        self::CONTENT    => 'Seite',
        self::USER_MAIL  => 'E-Mail an Nutzer',
        self::ADMIN_MAIL => 'E-Mail an Admin',
        self::DOCUMENT   => 'Dokument',
    ];
}
