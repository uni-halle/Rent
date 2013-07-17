<?php

namespace UniHalle\RentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SiteType extends AbstractType
{
    private $translator;
    private $isMail;

    public function __construct($translator, $isMail = false)
    {
        $this->translator = $translator;
        $this->isMail = $isMail;
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

        if ($this->isMail) {
            $builder->add(
                'subject',
                'text',
                array(
                    'label' => $this->translator->trans('Betreff'),
                    'attr'  => array(
                        'class' => 'input-xxlarge'
                    )
                )
            );

            $builder->add(
                'content',
                null,
                array(
                    'label' => $this->translator->trans('Inhalt'),
                    'attr' => array(
                        'class' => 'input-xxlarge',
                        'rows'  => 10
                    )
                )
            );
        } else {
            $builder->add(
                'content',
                'ckeditor',
                array(
                    'label' => $this->translator->trans('Inhalt'),
                    'config' => array(
                        'toolbar' => array(
                            array(
                                'name'  => 'basicstyles',
                                'items' => array('Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'),
                            ),
                            array(
                                'name' => 'paragraph',
                                'items' => array('NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl')
                            ),
                            '/',
                            array(
                                'name'  => 'styles',
                                'items' => array('Styles','Format','Font','FontSize'),
                            ),
                        )
                    )
                )
            );
        }
    }

    public function getName()
    {
        return 'category';
    }
}
