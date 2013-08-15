<?php

namespace UniHalle\RentBundle\Controller;

use UniHalle\RentBundle\Entity\User;
use UniHalle\RentBundle\Types\PersonType;
use UniHalle\RentBundle\Types\UserStatusType;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    const USERS_PER_PAGE = 15;

    /**
     * @Route("/list/{status}", name="user_index")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function indexAction(Request $request, $status = 'active')
    {
        $em = $this->getDoctrine()->getManager();
        $usersQuery = $em->getRepository('RentBundle:User')->findBy(
            array('status' => $status),
            array('username' => 'ASC')
        );

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $usersQuery,
            $request->get('page', 1),
            UserController::USERS_PER_PAGE
        );

        if (!$request->get('sort') && !$request->get('direction')) {
            $pagination->setParam('sort', 'u.username');
            $pagination->setParam('direction', 'asc');
        }

        return $this->render(
            'RentBundle:User:index.html.twig',
            array(
                'users'         => $pagination,
                'sortDirection' => $request->get('direction', 'asc'),
                'status'        => $status
            )
        );
    }

    /**
     * @Route("/update/{status}/{id}/{new_status}", name="user_status")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function statusAction(Request $request, $status, $id, $new_status)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('RentBundle:User')->findOneById($id);
        if (!$user) {
            throw $this->createNotFoundException('Nutzer wurde nicht gefunden.');
        }

        if ($user->getUsername() == $this->get('security.context')->getToken()->getUser()->getUsername()) {
            throw new \Exception('Der Status des eigenen Accounts kann nicht geändert werden.');
        }

        if ($new_status == 'active') {
            $user->setStatus(UserStatusType::ACTIVE);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('Nutzer wurde freigeschaltet.')
            );
            $this->sendUserStatusNotfication($user->getId(), 'active');
        } else if ($new_status == 'disabled') {
            $user->setStatus(UserStatusType::DISABLED);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('Nutzer wurde gesperrt.')
            );
            $this->sendUserStatusNotfication($user->getId(), 'disabled');
        } else {
            throw new \Exception('Ungültiger Status');
        }

        return $this->redirect($this->generateUrl('user_index', array('status' => $status)));
    }

    /**
     * @Route("/delete/{id}", name="user_delete")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('RentBundle:User')->findOneById($id);
        if (!$user) {
            throw $this->createNotFoundException('Nutzer wurde nicht gefunden.');
        }

        if ($user->getUsername() == $this->get('security.context')->getToken()->getUser()->getUsername()) {
            throw new \Exception('Der eigene Account kann nicht gelöscht werden.');
        }

        if ($request->isMethod('POST') && $request->request->get('confirmed') == 1) {
            $this->sendUserStatusNotfication($user->getId(), 'deleted');
            $em->remove($user);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('Nutzer wurde gelöscht.')
            );
            return $this->redirect($this->generateUrl('user_index'));
        }

        return $this->render(
            'RentBundle:User:delete.html.twig',
            array('user' => $user)
        );
    }

    /**
     * @Route("/register", name="user_register")
     */
    public function registerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $site = $em->getRepository('RentBundle:Site')
                   ->findOneByIdentifier('register');
        if (!$site) {
            throw $this->createNotFoundException('Seite wurde nicht gefunden.');
        }

        if ($request->isMethod('POST')) {
            $email = $request->get('inputEmail');
            $nkz   = $request->get('inputNkz');
            if ($email === '' || $nkz === '') {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    $this->get('translator')->trans('Bitte geben Sie ihre E-Mail Adresse und ihr Nutzerkennzeichen an.')
                );
                return $this->render('RentBundle:User:register_form.html.twig', array('site' => $site));
            }

            $user = $em->getRepository('RentBundle:User')->findOneByUsername($nkz);
            if ($user) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    $this->get('translator')->trans('Sie sind bereits für den Geräteverleih registriert.')
                );
                return $this->render('RentBundle:User:register_form.html.twig', array('site' => $site));
            }

            $userData = $this->getUserData($nkz);

            if (!$userData) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    $this->get('translator')->trans('Es wurde kein Benutzer mit dem Kennzeichen "'.$nkz.'" gefunden.')
                );
                return $this->render('RentBundle:User:register_form.html.twig', array('site' => $site));
            }

            if ($userData['email'] != $email) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    $this->get('translator')->trans('Ihr E-Mail Adresse konnte ihrem Nutzerkennzeichen nicht zugeordnet werden.')
                );
                return $this->render('RentBundle:User:register_form.html.twig', array('site' => $site));
            }

            $isAdmin = in_array($nkz, $this->container->getParameter('admin_users'));
            $user = new User();
            $user->setMail($email);
            $user->setUsername($nkz);
            $user->setName($userData['name']);
            $user->setSurname($userData['surname']);
            $user->setPersonType(PersonType::mapMluPersonType($userData['mluPersonType']));
            $user->setStatus($isAdmin ? UserStatusType::ACTIVE : UserStatusType::WAITING);
            $em->persist($user);
            $em->flush();

            $this->sendRegisterNotification($nkz);

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('Ihre Registrierung wurde vorgemerkt. Sobald ihr Account freigeschaltet wird, erhalten sie eine E-Mail mit weiteren Hinweisen.')
            );
            return $this->redirect($this->generateUrl('index'));
        }

        return $this->render('RentBundle:User:register_form.html.twig', array('site' => $site));
    }

    private function getUserData($uid)
    {
        $ldap = ldap_connect($this->container->getParameter('ldap_host'), $this->container->getParameter('ldap_port'));
        if (!$ldap) {
            throw new \Exception('LDAP Verbindung fehlgeschlagen');
        }
        if (!ldap_bind($ldap, $this->container->getParameter('ldap_admin_user'), $this->container->getParameter('ldap_admin_pass'))) {
            throw new \Exception('LDAP Authentifizierung fehlgeschlagen');
        }

        $result = ldap_search($ldap, $this->container->getParameter('ldap_user_basedn'), '(uid='.$uid.')');
        $data = ldap_get_entries($ldap, $result);
        $entryCount = ldap_count_entries($ldap, $result);
        ldap_close($ldap);

        if ($entryCount != 1) {
            return false;
        }

        return array(
            'email'         => $data[0]['mail'][0],
            'surname'       => $data[0]['sn'][0],
            'name'          => $data[0]['givenname'][0],
            'mluPersonType' => $data[0]['mlupersontype'][0]
        );
    }

    private function sendRegisterNotification($uid)
    {
        $em = $this->getDoctrine()->getManager();
        $config = $em->getRepository('RentBundle:Configuration');
        $user = $em->getRepository('RentBundle:User')->findOneByUsername($uid);
        if (!$user) {
            throw $this->createNotFoundException('Nutzer wurde nicht gefunden.');
        }

        $mail = $em->getRepository('RentBundle:Site')->findOneByIdentifier('mailUserRegistered');
        if (!$mail) {
            throw $this->createNotFoundException('E-Mail Inhalt wurde nicht gefunden.');
        }

        $content = $mail->getContent();
        $content = str_replace('{USER.SURNAME}', $user->getSurname(), $content);
        $content = str_replace('{USER.NAME}', $user->getName(), $content);
        $content = str_replace('{USER.MAIL}', $user->getMail(), $content);
        $content = str_replace('{USER.NKZ}', $user->getUsername(), $content);
        $content = str_replace('{USER.ACCOUNT_TYPE}', PersonType::getAccountTypeName($user->getPersonType()), $content);

        $message = \Swift_Message::newInstance()->setSubject($mail->getSubject())
                                                ->setFrom($config->getValue('mailSender'))
                                                ->setTo($config->getValue('adminMail'))
                                                ->setBody($content);

        $this->get('mailer')->send($message);
    }

    private function sendUserStatusNotfication($uid, $status)
    {
        $em = $this->getDoctrine()->getManager();
        $config = $em->getRepository('RentBundle:Configuration');
        $user = $em->getRepository('RentBundle:User')->findOneById($uid);
        if (!$user) {
            throw $this->createNotFoundException('Nutzer wurde nicht gefunden.');
        }

        $mail = null;
        if ($status == 'active') {
            $mail = $em->getRepository('RentBundle:Site')->findOneByIdentifier('mailRegAccepted');
        } else if ($status == 'disabled') {
            $mail = $em->getRepository('RentBundle:Site')->findOneByIdentifier('mailRegDisabled');
        } else if ($status == 'deleted') {
            $mail = $em->getRepository('RentBundle:Site')->findOneByIdentifier('mailRegDeleted');
        }
        if (!$mail) {
            throw $this->createNotFoundException('E-Mail Inhalt wurde nicht gefunden.');
        }

        $content = $mail->getContent();
        $content = str_replace('{USER.SURNAME}', $user->getSurname(), $content);
        $content = str_replace('{USER.NAME}', $user->getName(), $content);
        $content = str_replace('{USER.MAIL}', $user->getMail(), $content);
        $content = str_replace('{USER.NKZ}', $user->getUsername(), $content);
        $content = str_replace('{USER.ACCOUNT_TYPE}', PersonType::getAccountTypeName($user->getPersonType()), $content);

        $message = \Swift_Message::newInstance()->setSubject($mail->getSubject())
                                                ->setFrom($config->getValue('mailSender'))
                                                ->setTo($user->getMail())
                                                ->setBody($content);

        $this->get('mailer')->send($message);
    }
}
