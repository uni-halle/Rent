<?php

namespace UniHalle\RentBundle\Controller;

use UniHalle\RentBundle\Entity\Device;
use UniHalle\RentBundle\Form\Type\DeviceType;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/device")
 */
class DeviceController extends Controller
{
    /**
     * @Route("/", name="device_index")
     */
    public function indexAction()
    {
        $devices = $this->getDoctrine()
                        ->getManager()
                        ->getRepository('RentBundle:Device')
                        ->findBy(
                            array(),
                            array('name' => 'ASC')
                        );

        return $this->render(
            'RentBundle:Device:index.html.twig',
            array('devices' => $devices)
        );
    }

    /**
     * @Route("/{id}", name="device_show", requirements={"id"="\d+"})
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $device = $em->getRepository('RentBundle:Device')
                     ->findOneById($id);
        if (!$device) {
            throw $this->createNotFoundException('Gerät wurde nicht gefunden.');
        }

        return $this->render(
            'RentBundle:Device:show.html.twig',
            array('device' => $device)
        );
    }

    /**
     * @Route("/new/{category_id}", name="device_new")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function newAction(Request $request, $category_id = 0)
    {
        $em = $this->getDoctrine()->getManager();
        $device = new Device();
        if ($category_id > 0) {
            $category = $em->getRepository('RentBundle:Category')
                           ->findOneById($category_id);
            if (!$category) {
                throw $this->createNotFoundException('Kategorie wurde nicht gefunden.');
            }
            $device->setCategory($category);
        }
        $form = $this->createForm(new DeviceType($this->get('translator')), $device);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em->persist($device);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('Gerät wurde hinzugefügt')
                );

                return $this->redirect($this->generateUrl('category_show', array(
                    'id' => $device->getCategory()->getId()
                )));
            }
        }

        return $this->render(
            'RentBundle:Device:new.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/update/{id}", name="device_update")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $device = $em->getRepository('RentBundle:Device')
                     ->findOneById($id);
        if (!$device) {
            throw $this->createNotFoundException('Gerät wurde nicht gefunden.');
        }

        $form = $this->createForm(new DeviceType($this->get('translator')), $device);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('Gerät wurde aktualisiert')
                );
                return $this->redirect($this->generateUrl('category_show', array(
                    'id' => $device->getCategory()->getId()
                )));
            }
        }

        return $this->render(
            'RentBundle:Device:update.html.twig',
            array(
                'form' => $form->createView(),
                'id'   => $device->getId()
            )
        );
    }

    /**
     * @Route("/delete/{id}", name="device_delete")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $device = $em->getRepository('RentBundle:Device')
                     ->findOneById($id);
        if (!$device) {
            throw $this->createNotFoundException('Gerät wurde nicht gefunden.');
        }

        if ($request->isMethod('POST') && $request->request->get('confirmed') == 1) {
            $categoryId = $device->getCategory()->getId();
            $em->remove($device);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('Gerät wurde gelöscht')
            );
            return $this->redirect($this->generateUrl('category_show', array(
                'id' => $categoryId
            )));
        }

        return $this->render(
            'RentBundle:Device:delete.html.twig',
            array('device' => $device)
        );
    }
}
