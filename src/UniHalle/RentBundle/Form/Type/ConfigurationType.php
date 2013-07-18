<?php

namespace UniHalle\RentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigurationType extends AbstractType
{
    private $translator;
    private $inputType;

    public function __construct($translator, $inputType = null)
    {
        $this->translator = $translator;
        $this->inputType = $inputType;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            null,
            array(
                'label'     => $this->translator->trans('Name'),
                'read_only' => true
            )
        );

        $builder->add(
            'value',
            $this->inputType,
            array(
                'label' => $this->translator->trans('Wert'),
                'attr'  => array(
                    'class' => 'input-xlarge',
                    'rows'  => '10'
                )
            )
        );
    }

    public function getName()
    {
        return 'configuration';
    }
}
