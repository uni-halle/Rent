<?php

namespace UniHalle\RentBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use UniHalle\RentBundle\Entity\Configuration;

class LoadConfiguration extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $blockingPeriod = new Configuration();
        $blockingPeriod->setName('Entleihsperre');
        $blockingPeriod->setIdentifier('blockingPeriod');
        $blockingPeriod->setValue('2');
        $manager->persist($blockingPeriod);

        $adminMail = new Configuration();
        $adminMail->setName('Administrative E-Mail');
        $adminMail->setIdentifier('adminMail');
        $adminMail->setValue('example@foo.org');
        $manager->persist($adminMail);

        $mailSender = new Configuration();
        $mailSender->setName('E-Mail Absender');
        $mailSender->setIdentifier('mailSender');
        $mailSender->setValue('example@foo.org');
        $manager->persist($mailSender);

        $holidays = new Configuration();
        $holidays->setName('Feiertage');
        $holidays->setIdentifier('holidays');
        $holidays->setValue('03.10.2013'."\n".'31.10.2013'."\n".'25.12.2013'."\n".'26.12.2013'."\n".'01.01.2014'."\n".
                            '06.01.2014'."\n".'18.04.2014'."\n".'21.04.2014'."\n".'01.05.2014'."\n".'29.05.2014'."\n".
                            '09.06.2014'."\n".'03.10.2014'."\n".'31.10.2014'."\n".'25.12.2014'."\n".'26.12.2014');
        $manager->persist($holidays);

        $manager->flush();
    }

    public function getOrder()
    {
        return 3;
    }
}
