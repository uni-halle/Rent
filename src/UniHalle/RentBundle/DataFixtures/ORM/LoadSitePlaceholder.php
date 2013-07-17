<?php

namespace UniHalle\RentBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use UniHalle\RentBundle\Entity\SitePlaceholder;

class LoadSitePlaceholder extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $pSurname = new SitePlaceholder();
        $pSurname->setName('Nachname');
        $pSurname->setPlaceholder('{USER.SURNAME}');
        $manager->persist($pSurname);
        $this->addReference('placeholder-surname', $pSurname);

        $pName = new SitePlaceholder();
        $pName->setName('Vorname');
        $pName->setPlaceholder('{USER.NAME}');
        $manager->persist($pName);
        $this->addReference('placeholder-name', $pName);

        $pMail = new SitePlaceholder();
        $pMail->setName('E-Mail');
        $pMail->setPlaceholder('{USER.MAIL}');
        $manager->persist($pMail);
        $this->addReference('placeholder-mail', $pMail);

        $pDateNow = new SitePlaceholder();
        $pDateNow->setName('Aktuelles Datum');
        $pDateNow->setPlaceholder('{DATE.NOW}');
        $manager->persist($pDateNow);
        $this->addReference('placeholder-dateNow', $pDateNow);

        $pDateStart = new SitePlaceholder();
        $pDateStart->setName('Beginn (Datum)');
        $pDateStart->setPlaceholder('{DATE.START}');
        $manager->persist($pDateStart);
        $this->addReference('placeholder-dateStart', $pDateStart);

        $pDateEnd = new SitePlaceholder();
        $pDateEnd->setName('Ende (Datum)');
        $pDateEnd->setPlaceholder('{DATE.END}');
        $manager->persist($pDateEnd);
        $this->addReference('placeholder-dateEnd', $pDateEnd);

        $pDeviceName = new SitePlaceholder();
        $pDeviceName->setName('Gerät');
        $pDeviceName->setPlaceholder('{DEVICE.NAME}');
        $manager->persist($pDeviceName);
        $this->addReference('placeholder-deviceName', $pDeviceName);

        $pSerialNumber = new SitePlaceholder();
        $pSerialNumber->setName('Ende (Datum)');
        $pSerialNumber->setPlaceholder('{DEVICE.SERIAL_NUMBER}');
        $manager->persist($pSerialNumber);
        $this->addReference('placeholder-serialNumber', $pSerialNumber);

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
