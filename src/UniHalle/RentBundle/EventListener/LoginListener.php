<?php

namespace UniHalle\RentBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use UniHalle\RentBundle\Types\PersonType;

class LoginListener
{
    private $doctrine;

    public function __construct(Doctrine $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function onLogin(InteractiveLoginEvent $event)
    {
        $eventUser = $event->getAuthenticationToken()->getUser();
        if ($eventUser && get_class($eventUser) == 'IMAG\LdapBundle\User\LdapUser') {
            $em = $this->doctrine->getManager();
            $user = $em->getRepository('RentBundle:User')->findOneByUsername($eventUser->getUsername());
            $user->setSurname($eventUser->getAttribute('sn'));
            $user->setName($eventUser->getAttribute('givenname'));
            $user->setMail($eventUser->getAttribute('mail'));
            $user->setPersonType(PersonType::mapMluPersonType($eventUser->getAttribute('mlupersontype')));
            $em->flush();
        }
    }
}
