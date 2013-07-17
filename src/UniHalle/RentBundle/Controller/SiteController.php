<?php

namespace UniHalle\RentBundle\Controller;

use UniHalle\RentBundle\Entity\Site;
use UniHalle\RentBundle\Form\Type\SiteType;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/site")
 */
class SiteController extends Controller
{
    /**
     * @Route("/", name="site_index")
     */
    public function indexAction()
    {
        $content = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('RentBundle:Site')
                        ->findBy(
                            array('type' => \UniHalle\RentBundle\Types\SiteType::CONTENT),
                            array('name' => 'ASC')
                        );

        $documents = $this->getDoctrine()
                          ->getManager()
                          ->getRepository('RentBundle:Site')
                          ->findBy(
                              array('type' => \UniHalle\RentBundle\Types\SiteType::DOCUMENT),
                              array('name' => 'ASC')
                          );

        $userMails = $this->getDoctrine()
                          ->getManager()
                          ->getRepository('RentBundle:Site')
                          ->findBy(
                              array('type' => \UniHalle\RentBundle\Types\SiteType::USER_MAIL),
                              array('name' => 'ASC')
                          );

        $adminMails = $this->getDoctrine()
                           ->getManager()
                           ->getRepository('RentBundle:Site')
                           ->findBy(
                               array('type' => \UniHalle\RentBundle\Types\SiteType::ADMIN_MAIL),
                               array('name' => 'ASC')
                           );

        return $this->render(
            'RentBundle:Site:index.html.twig',
            array(
                'content'    => $content,
                'documents'  => $documents,
                'userMails'  => $userMails,
                'adminMails' => $adminMails
            )
        );
    }

    /**
     * @Route("/{identifier}", name="site_show")
     */
    public function showAction($identifier)
    {
        $em = $this->getDoctrine()->getManager();
        $site = $em->getRepository('RentBundle:Site')
                   ->findOneByIdentifier($identifier);
        if (!$site) {
            throw $this->createNotFoundException('Seite wurde nicht gefunden.');
        }

        return $this->render('RentBundle:Site:show.html.twig', array( 'site'  => $site ));
    }

    /**
     * @Route("/update/{id}", name="site_update")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $site = $em->getRepository('RentBundle:Site')
                   ->findOneById($id);
        if (!$site) {
            throw $this->createNotFoundException('Seite wurde nicht gefunden.');
        }

        $form = $this->createForm(
            new SiteType(
                $this->get('translator'),
                $site->getType() == \UniHalle\RentBundle\Types\SiteType::USER_MAIL || $site->getType() == \UniHalle\RentBundle\Types\SiteType::ADMIN_MAIL),
            $site);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('Inhalt wurde aktualisiert')
                );
                return $this->redirect($this->generateUrl('site_index'));
            }
        }

        return $this->render(
            'RentBundle:Site:update.html.twig',
            array(
                'form' => $form->createView(),
                'site' => $site
            )
        );
    }
}
