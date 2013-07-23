<?php

namespace UniHalle\RentBundle\Controller;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/")
 */
class IndexController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $site = $em->getRepository('RentBundle:Site')
                   ->findOneByIdentifier('index');
        if (!$site) {
            throw $this->createNotFoundException('Seite wurde nicht gefunden.');
        }
        $contact = $em->getRepository('RentBundle:Site')
                      ->findOneByIdentifier('indexContact');
        if (!$contact) {
            throw $this->createNotFoundException('Kontaktdaten wurden nicht gefunden.');
        }

        return $this->render('RentBundle:Index:index.html.twig',
            array(
                'site'    => $site,
                'contact' => $contact
            )
        );
    }
}
