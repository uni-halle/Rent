<?php

namespace UniHalle\RentBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BookingType extends AbstractType
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
            'dateFrom',
            null,
            array('label' => $this->translator->trans('Von'))
        );
        $builder->add(
            'dateTo',
            null,
            array('label' => $this->translator->trans('Bis'))
        );


    }

    public function getName()
    {
        return 'booking';
    }
}
