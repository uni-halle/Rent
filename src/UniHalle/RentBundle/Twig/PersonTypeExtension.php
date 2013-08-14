<?php

namespace UniHalle\RentBundle\Twig;

use UniHalle\RentBundle\Types\PersonType;

class PersonTypeExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'personTypeName' => new \Twig_Filter_Method($this, 'personTypeName'),
        );
    }

    public function personTypeName($personType)
    {
        $labelClass = '';
        switch ($personType) {
            case PersonType::EMPLOYEE:
                $labelClass = 'label-success';
                break;
            case PersonType::STUDENT:
                $labelClass = 'label-important';
                break;
            case PersonType::GUEST:
                $labelClass = 'label-info';
                break;
            default:
                $labelClass = 'label-default';
                break;
        }

        return '<span class="label '.$labelClass.'">'.PersonType::getAccountTypeName($personType).'</span>';
    }

    public function getName()
    {
        return 'personType_extension';
    }
}
