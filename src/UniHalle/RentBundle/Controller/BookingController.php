<?php

namespace UniHalle\RentBundle\Controller;

use UniHalle\RentBundle\Entity\Device;
use UniHalle\RentBundle\Entity\Booking;
use UniHalle\RentBundle\Entity\Configuration;
use UniHalle\RentBundle\Form\Type\BookingType;
use UniHalle\RentBundle\Types\BookingStatusType;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/booking")
 */
class BookingController extends Controller
{
    /**
     * @Route("/", name="booking_index")
     * @Secure(roles="ROLE_USER")
     * @todo: switch user/admin: show bookings by user/all
     * @todo: use pagination
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $bookings = $em->getRepository('RentBundle:Booking')
                       ->findBy(
                           array(),
                           array('dateFrom' => 'ASC')
                       );

        return $this->render(
            'RentBundle:Booking:index.html.twig',
            array('bookings' => $bookings)
        );
    }

    /**
     * @Route("/{id}", name="booking_show", requirements={"id"="\d+"})
     * @Secure(roles="ROLE_USER")
     * @todo enable user check
     */
    public function showAction($id)
    {
        $securityContext = $this->get('security.context');
        $em = $this->getDoctrine()->getManager();
        $booking = $em->getRepository('RentBundle:Booking')
                      ->findOneById($id);

        if (!$device) {
            throw $this->createNotFoundException('Buchung wurde nicht gefunden.');
        }

        if (false /*$booking->getUser()->getId() != $securityContext->getToken()->getUser()->getId()*/) {
            throw new AccessDeniedHttpException('Sie dürfen diese Buchung nicht einsehen.');
        }

        return $this->render(
            'RentBundle:Booking:show.html.twig',
            array('booking' => $booking)
        );
    }

    /**
     * @Route("/new/{device_id}/{start_display}/{start_date}/{end_date}", name="booking_new")
     * @Secure(roles="ROLE_USER")
     * @todo: set correct user
     */
    public function newAction(Request $request, $device_id, $start_display = null, $start_date = null, $end_date = null)
    {
        $em = $this->getDoctrine()->getManager();
        $booking = new Booking();

        $device = $em->getRepository('RentBundle:Device')
                     ->findOneById($device_id);
        if (!$device) {
            throw $this->createNotFoundException('Gerät wurde nicht gefunden.');
        }

        $user = $em->getRepository('RentBundle:User')
                   ->findOneById(1);

        $booking->setDevice($device);
        $booking->setUser($user);

        if ($start_display !== null) {
            $startDisplay = new \DateTime($start_display . '-01 00:00:00');
        } else {
            $startDisplay = new \DateTime('first day of this month 00:00:00');
        }
        $endDisplay = clone $startDisplay;
        $intervalDay = new \DateInterval('P1D');
        $intervalDay->invert = 1;
        $endDisplay->add(new \DateInterval('P8M'))->add($intervalDay);

        $nextDisplay = clone $endDisplay;
        $nextDisplay->add(new \DateInterval('P1D'));

        if ($startDisplay > new \DateTime('first day of this month 00:00:00')) {
            $prevDisplay = clone $startDisplay;
            $intervalNineMonths = new \DateInterval('P8M');
            $intervalNineMonths->invert = -1;
            $prevDisplay->add($intervalNineMonths);
        } else {
            $prevDisplay = null;
        }

        $dates = $this->getMonthsArray($startDisplay, $endDisplay, $device_id);

        $error = null;
        $startDateObj = ($start_date !== null) ? new \DateTime($start_date . ' 00:00:00') : null;
        $endDateObj = ($end_date !== null) ? new \DateTime($end_date . ' 00:00:00') : null;

        if ($startDateObj !== null && $startDateObj < new \DateTime('today 00:00:00')) {
            $startDateObj = null;
            $start_date = null;
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('Die Entleihung kann nicht in der Vergangenheit beginnen.')
            );
        }

        if ($startDateObj !== null && $endDateObj !== null) {
            if ($startDateObj > $endDateObj) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    $this->get('translator')->trans('Die Rückgabe kann nicht vor Beginn der Entleihung erfolgen.')
                );
            } elseif ($em->getRepository('RentBundle:Booking')->bookingExistsInPeriod($device_id, $startDateObj, $endDateObj)) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    $this->get('translator')->trans('In diesem Zeitraum liegt bereits eine Buchung.')
                );
            } else {
                $booking->setDateFrom($startDateObj);
                $booking->setDateTo($endDateObj);

                $form = $this->createForm(new BookingType($this->get('translator'), $this->get('security.context')), $booking);

                return $this->render('RentBundle:Booking:new_form.html.twig', array(
                    'device'       => $device,
                    'form'         => $form->createView(),
                    'startDateObj' => $startDateObj,
                    'endDateObj'   => $endDateObj,
                ));
            }
        }

        $form = $this->createForm(new BookingType($this->get('translator'), $this->get('security.context')), $booking);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if (($booking->getDateFrom() <= $booking->getDateTo()) &&
                    !$em->getRepository('RentBundle:Booking')->bookingExistsInPeriod($device_id, $booking->getDateFrom(), $booking->getDateTo()) &&
                    $form->isValid()) {
                $booking->setStatus(BookingStatusType::PRELIMINARY);
                $em->persist($booking);
                $em->flush();

                $this->get('mailer')->send($this->getBookingMessage($booking->getId()));

                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('Ihre Buchung wurde vorläufig angenommen. Sobald ihre Buchung genehmigt wurde, erhalten Sie eine Benachrichtigung per E-Mail.')
                );

                return $this->redirect($this->generateUrl('booking_index'));
            } else {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    $this->get('translator')->trans('Ein Fehler ist bei der Bearbeitung ihrer Buchung aufgetreten.')
                );
            }
        }

        return $this->render(
            'RentBundle:Booking:new.html.twig',
            array(
                'device'          => $device,
                'dates'           => $dates,
                'start_display'   => $startDisplay->format('Y-m'),
                'nextDisplayDate' => $nextDisplay,
                'prevDisplayDate' => $prevDisplay,
                'startDate'       => $start_date,
                'startDateObj'    => $startDateObj,
                'dateToChoose'    => ($start_date !== null) ? 'end' : 'start'
            )
        );
    }

    /**
     * @Route("/update/{id}", name="booking_update")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $booking = $em->getRepository('RentBundle:Booking')
                      ->findOneById($id);
        if (!$booking) {
            throw $this->createNotFoundException('Buchung wurde nicht gefunden.');
        }

        $form = $this->createForm(new BookingType($this->get('translator'), $this->get('security.context')), $booking);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if (($booking->getDateFrom() <= $booking->getDateTo()) &&
                    !$em->getRepository('RentBundle:Booking')->bookingExistsInPeriod($booking->getDevice()->getId(), $booking->getDateFrom(), $booking->getDateTo(), $booking) &&
                    $form->isValid()) {
                $em->flush();

                $this->get('mailer')->send($this->getBookingMessage($booking->getId()), true);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('Buchung wurde aktualisiert')
                );
                return $this->redirect($this->generateUrl('booking_index'));
            } else {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    $this->get('translator')->trans('Eine Buchung kann in diesem Zeitraum nicht durchgeführt werden.')
                );
            }
        }

        return $this->render(
            'RentBundle:Booking:update.html.twig',
            array(
                'form' => $form->createView(),
                'id'   => $booking->getId()
            )
        );
    }

    /**
     * @Route("/delete/{id}", name="booking_delete")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $booking = $em->getRepository('RentBundle:Booking')
                      ->findOneById($id);
        if (!$booking) {
            throw $this->createNotFoundException('Buchung wurde nicht gefunden.');
        }

        if ($request->isMethod('POST') && $request->request->get('confirmed') == 1) {
            $em->remove($booking);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('Buchung wurde gelöscht')
            );

            return $this->redirect($this->generateUrl('booking_index'));
        }

        return $this->render(
            'RentBundle:Booking:delete.html.twig',
            array('booking' => $booking)
        );
    }

    /**
     * @Route("/status/{id}/{status}", name="booking_status")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function statusAction($id, $status)
    {
        $em = $this->getDoctrine()->getManager();
        $booking = $em->getRepository('RentBundle:Booking')
                      ->findOneById($id);
        if (!$booking) {
            throw $this->createNotFoundException('Buchung wurde nicht gefunden.');
        }
        switch ($status) {
            case 'approved':
                $booking->setStatus(BookingStatusType::APPROVED);
                $this->get('mailer')->send($this->getBookingMessage($booking->getId()));
                break;
            case 'canceled':
                $booking->setStatus(BookingStatusType::CANCELED);
                $this->get('mailer')->send($this->getBookingMessage($booking->getId()));
                break;
            case 'inRent':
                $booking->setStatus(BookingStatusType::IN_RENT);
                break;
            case 'gotBack':
                $booking->setStatus(BookingStatusType::GOT_BACK);
                break;
            case 'preliminary':
                $booking->setStatus(BookingStatusType::PRELIMINARY);
                break;
            default:
                $this->get('session')->getFlashBag()->add(
                    'error',
                    $this->get('translator')->trans('Ungültiger Status.')
                );
                return $this->redirect($this->generateUrl('booking_index'));
                break;
        }
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            $this->get('translator')->trans('Buchung wurde aktualisiert.')
        );
        return $this->redirect($this->generateUrl('booking_index'));
    }

    /**
     * @Route("/inRentDoc/{id}", name="booking_inRentDoc")
     * @Secure(roles="ROLE_ADMIN")
     * @todo: update template
     */
    public function inRentDocAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $booking = $em->getRepository('RentBundle:Booking')
                      ->findOneById($id);
        if (!$booking) {
            throw $this->createNotFoundException('Buchung wurde nicht gefunden.');
        }

        return $this->render(
            'RentBundle:Booking:doc_inRent.html.twig',
            array('booking' => $booking)
        );
    }

    /**
     * @Route("/gotBackDoc/{id}", name="booking_gotBackDoc")
     * @Secure(roles="ROLE_ADMIN")
     * @todo: update template
     */
    public function gotBackDocAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $booking = $em->getRepository('RentBundle:Booking')
                      ->findOneById($id);
        if (!$booking) {
            throw $this->createNotFoundException('Buchung wurde nicht gefunden.');
        }

        return $this->render(
            'RentBundle:Booking:doc_gotBack.html.twig',
            array('booking' => $booking)
        );
    }

    private function getMonthsArray($startDate, $endDate, $device_id)
    {
        $em = $this->getDoctrine()->getManager();
        $currentBookings = $em->getRepository('RentBundle:Booking')->getCurrentBookings($device_id);
        $holidays = $em->getRepository('RentBundle:Configuration')->getHolidays();

        $currentDate = clone $startDate;
        $dates = array();
        $month = array(
            'dates'   => array(),
            'month'   => clone $currentDate,
            'prepend' => $currentDate->format('N')-1
        );
        do {
            $booked = 0;
            foreach ($currentBookings as $b) {
                if ($b->getDateFrom() <= $currentDate && $b->getDateBlockedUntil() >= $currentDate) {
                    $booked = 1;
                }
            }

            if (count($month['dates']) > 0 && $month['month']->format('m') != $currentDate->format('m')) {
                $dates[] = $month;
                $month = array(
                    'dates'   => array(),
                    'month'   => clone $currentDate,
                    'prepend' => $currentDate->format('N')-1
                );
            }

            $month['dates'][] = array(
                'date'    => clone $currentDate,
                'class'   => ($currentDate->format('N') >= 6 || in_array($currentDate, $holidays)) ? 'weekend' : ($booked ? 'booked' : 'free')
            );
            $currentDate->add(new \DateInterval('P1D'));
        } while ($month['dates'][count($month['dates'])-1]['date'] < $endDate);
        $dates[] = $month;

        return $dates;
    }

    private function getBookingMessage($bookingId, $updated = false)
    {
        $em = $this->getDoctrine()->getManager();

        $config = $em->getRepository('RentBundle:Configuration');

        $booking = $em->getRepository('RentBundle:Booking')->findOneById($bookingId);
        if (!$booking) {
            throw $this->createNotFoundException('Buchung wurde nicht gefunden.');
        }

        $mail = null;
        $to = '';
        if ($updated) {
            $mail = $em->getRepository('RentBundle:Site')->findOneByIdentifier('mailRentalUpdated');
            $to = $booking->getUser()->getMail();
        } else if ($booking->getStatus() == BookingStatusType::APPROVED) {
            $mail = $em->getRepository('RentBundle:Site')->findOneByIdentifier('mailRentalAccpeted');
            $to = $booking->getUser()->getMail();
        } else if ($booking->getStatus() == BookingStatusType::CANCELED) {
            $mail = $em->getRepository('RentBundle:Site')->findOneByIdentifier('mailRentalDenied');
            $to = $booking->getUser()->getMail();
        } else if ($booking->getStatus() == BookingStatusType::PRELIMINARY) {
            $mail = $em->getRepository('RentBundle:Site')->findOneByIdentifier('mailNewRent');
            $to = $config->getValue('adminMail');
        }

        if (!$mail) {
            throw $this->createNotFoundException('E-Mail Inhalt wurde nicht gefunden.');
        }

        $subject = $mail->getSubject();

        $content = $mail->getContent();
        $content = str_replace('{USER.SURNAME}', $booking->getUser()->getSurname(), $content);
        $content = str_replace('{USER.NAME}', $booking->getUser()->getName(), $content);
        $content = str_replace('{USER.MAIL}', $booking->getUser()->getMail(), $content);
        $content = str_replace('{DATE.NOW}', date('d.m.Y'), $content);
        $content = str_replace('{DATE.START}', $booking->getDateFrom()->format('d.m.Y'), $content);
        $content = str_replace('{DATE.END}', $booking->getDateTo()->format('d.m.Y'), $content);
        $content = str_replace('{DEVICE.NAME}', $booking->getDevice()->getName(), $content);
        $content = str_replace('{DEVICE.SERIAL_NUMBER}', $booking->getDevice()->getSerialNumber(), $content);

        $message = \Swift_Message::newInstance()->setSubject($mail->getSubject())
                                                ->setFrom($config->getValue('mailSender'))
                                                ->setTo($to)
                                                ->setBody($content);
        return $message;
    }
}
