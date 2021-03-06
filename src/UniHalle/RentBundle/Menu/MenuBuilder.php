<?php
namespace UniHalle\RentBundle\Menu;

use Knp\Menu\FactoryInterface;
use Mopa\Bundle\BootstrapBundle\Navbar\AbstractNavbarMenuBuilder;
use Symfony\Component\HttpFoundation\Request;

class MenuBuilder extends AbstractNavbarMenuBuilder
{
    private $security;

    public function __construct(FactoryInterface $factory, $security)
    {
        $this->security = $security;
        parent::__construct($factory);
    }

    public function createMenu(Request $request)
    {
        $menu = $this->createNavbarMenuItem();

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $menu->addChild('Geräte', array('route' => 'category_index'));
            $menu->addChild('Buchungen', array('route' => 'booking_index'));
            $menu->addChild('Nutzer', array('route' => 'user_index'));
            $menu->addChild('Inhalte', array('route' => 'site_index'));
            $menu->addChild('Konfiguration', array('route' => 'configuration_index'));
        } else if ($this->security->isGranted('ROLE_USER')) {
            $menu->addChild('Geräte', array('route' => 'category_index'));
            $menu->addChild('Meine Buchungen', array('route' => 'booking_index'));
        } else {
            $menu->addChild('Registrierung', array('route' => 'user_register'));
        }
        return $menu;
    }

    public function createRightMenu(Request $request)
    {
        $menu = $this->createNavbarMenuItem();
        $menu->addChild('Logout', array('route' => 'logout'));
        $menu->setChildrenAttribute('class', 'nav pull-right');
        return $menu;
    }
}
