<?php

namespace UniHalle\RentBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use UniHalle\RentBundle\Types\BookingStatusType;

class BookingExtensionType extends AbstractType
{
    private $translator;
    private $security;

    public function __construct($translator, $security)
    {
        $this->translator = $translator;
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'dateTo',
            null,
            array('label' => $this->translator->trans('Bis'))
        );
    }

    public function getName()
    {
        return 'bookingExtension';
    }
}
