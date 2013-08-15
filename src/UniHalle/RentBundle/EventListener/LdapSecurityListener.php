<?php

namespace UniHalle\RentBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use IMAG\LdapBundle\Event\LdapUserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use UniHalle\RentBundle\Types\UserStatusType;

class LdapSecurityListener implements EventSubscriberInterface
{
    private $doctrine;
    private $session;

    public function __construct(Doctrine $doctrine, $session)
    {
        $this->doctrine = $doctrine;
        $this->session = $session;
    }

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

        $em = $this->doctrine->getEntityManager();
        $user = $em->getRepository('RentBundle:User')->findOneByUsername($user->getUsername());
        if (!$user) {
            $this->session->getFlashBag()->add('error', 'Sie sind noch nicht für den Geräteverleih registriert.');
            throw new \Exception('Authentication error');
        }

        if ($user->getStatus() == UserStatusType::WAITING) {
            $this->session->getFlashBag()->add('error', 'Ihr Nutzerkonto wurde noch nicht freigeschaltet.');
            throw new \Exception('Authentication error');
        }

        if ($user->getStatus() == UserStatusType::DISABLED) {
            $this->session->getFlashBag()->add('error', 'Ihr Nutzerkonto wurde deaktiviert.');
            throw new \Exception('Authentication error');
        }

        if ($user->getStatus() != UserStatusType::ACTIVE) {
            $this->session->getFlashBag()->add('error', 'Ein Fehler ist bei der Autorisierung aufgetreten.');
            throw new \Exception('Authentication error');
        }
    }
}
