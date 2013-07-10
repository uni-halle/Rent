<?php

namespace UniHalle\RentBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DeviceType extends AbstractType
{
    private $translator;

    public function __construct($translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            null,
            array('label' => $this->translator->trans('Name'))
        );
        $builder->add(
            'serialNumber',
            null,
            array('label' => $this->translator->trans('Seriennummer'))
        );
        $builder->add(
            'deviceNumber',
            null,
            array('label' => $this->translator->trans('Gerätenummer'))
        );
        $builder->add(
            'description',
            null,
            array(
                'label' => $this->translator->trans('Beschreibung'),
                'attr'  => array(
                    'rows' => 10,
                    'class' => 'input-xxlarge'
                )
            )
        );

        $builder->add(
            'category',
            'entity',
            array(
                'class'    => 'UniHalle\RentBundle\Entity\Category',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                              ->orderBy('c.name', 'ASC');
                },
                'label'    => $this->translator->trans('Kategorie'),
                'required' => true,
                'empty_value' => 'Kategorie auswählen'
            )
        );
    }

    public function getName()
    {
        return 'device';
    }
}
