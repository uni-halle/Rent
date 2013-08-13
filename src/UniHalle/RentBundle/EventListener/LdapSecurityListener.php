<?php

namespace UniHalle\RentBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use IMAG\LdapBundle\Event\LdapUserEvent;

class LdapSecurityListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            \IMAG\LdapBundle\Event\LdapEvents::PRE_BIND => 'onPreBind',
        );
    }

    public function onPreBind(LdapUserEvent $event)
    {
        $user = $event->getUser();
        $user->addRole('ROLE_USER');
    }
}
