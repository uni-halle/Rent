<?php

namespace UniHalle\RentBundle\Controller;

use UniHalle\RentBundle\Entity\Configuration;
use UniHalle\RentBundle\Form\Type\ConfigurationType;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/configuration")
 */
class ConfigurationController extends Controller
{
    /**
     * @Route("/", name="configuration_index")
     */
    public function indexAction()
    {
        $configuration = $this->getDoctrine()
                              ->getManager()
                              ->getRepository('RentBundle:Configuration')
                              ->findBy(
                                  array(),
                                  array('name' => 'ASC')
                              );

        return $this->render(
            'RentBundle:Configuration:index.html.twig',
            array(
                'configuration' => $configuration,
            )
        );
    }

    /**
     * @Route("/update/{id}", name="configuration_update")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $config = $em->getRepository('RentBundle:Configuration')
                     ->findOneById($id);
        if (!$config) {
            throw $this->createNotFoundException('Eintrag wurde nicht gefunden.');
        }

        $inputType = null;
        switch ($config->getIdentifier()) {
            case 'blockingPeriod':
            case 'adminMail':
            case 'mailSender':
                $inputType = 'text';
                break;
            case 'holidays':
                $inputType = 'textarea';
                break;
        }

        $form = $this->createForm(new ConfigurationType($this->get('translator'), $inputType), $config);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('Konfiguration wurde aktualisiert.')
                );
                return $this->redirect($this->generateUrl('configuration_index'));
            }
        }

        return $this->render(
            'RentBundle:Configuration:update.html.twig',
            array(
                'form'   => $form->createView(),
                'config' => $config
            )
        );
    }
}
