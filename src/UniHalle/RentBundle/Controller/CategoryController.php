<?php

namespace UniHalle\RentBundle\Controller;

use UniHalle\RentBundle\Entity\Category;
use UniHalle\RentBundle\Form\Type\CategoryType;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/category")
 */
class CategoryController extends Controller
{
    /**
     * @Route("/", name="category_index")
     */
    public function indexAction()
    {
        $categories = $this->getDoctrine()
                           ->getManager()
                           ->getRepository('RentBundle:Category')
                           ->findBy(
                               array(),
                               array('name' => 'ASC')
                           );

        return $this->render(
            'RentBundle:Category:index.html.twig',
            array('categories' => $categories)
        );
    }

    /**
     * @Route("/{id}", name="category_show", requirements={"id"="\d+"})
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository('RentBundle:Category')
                       ->findOneById($id);
        if (!$category) {
            throw $this->createNotFoundException('Kategorie wurde nicht gefunden.');
        }

        $devices = $category->getDevices();

        return $this->render(
            'RentBundle:Device:index.html.twig',
            array(
                'devices'  => $devices,
                'category' => $category
            )
        );
    }

    /**
     * @Route("/new", name="category_new")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function newAction(Request $request)
    {
        $category = new Category();
        $form = $this->createForm(new CategoryType($this->get('translator')), $category);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($category);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('Kategorie wurde hinzugefügt')
                );

                return $this->redirect($this->generateUrl('category_index'));
            }
        }

        return $this->render(
            'RentBundle:Category:new.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/update/{id}", name="category_update")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository('RentBundle:Category')
                       ->findOneById($id);
        if (!$category) {
            throw $this->createNotFoundException('Kategorie wurde nicht gefunden.');
        }

        $form = $this->createForm(new CategoryType($this->get('translator')), $category);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('Kategorie wurde aktualisiert')
                );
                return $this->redirect($this->generateUrl('category_index'));
            }
        }

        return $this->render(
            'RentBundle:Category:update.html.twig',
            array(
                'form' => $form->createView(),
                'id'   => $category->getId()
            )
        );
    }

    /**
     * @Route("/delete/{id}", name="category_delete")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository('RentBundle:Category')
                       ->findOneById($id);
        if (!$category) {
            throw $this->createNotFoundException('Kategorie wurde nicht gefunden.');
        }

        if ($request->isMethod('POST') && $request->request->get('confirmed') == 1) {
            $em->remove($category);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('Kategorie wurde gelöscht')
            );
            return $this->redirect($this->generateUrl('category_index'));
        }

        return $this->render(
            'RentBundle:Category:delete.html.twig',
            array('category' => $category)
        );
    }
}
