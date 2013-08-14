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

        $pUid = new SitePlaceholder();
        $pUid->setName('Nutzerkennzeichen');
        $pUid->setPlaceholder('{USER.NKZ}');
        $manager->persist($pUid);
        $this->addReference('placeholder-uid', $pUid);

        $pAccountType = new SitePlaceholder();
        $pAccountType->setName('Accounttyp');
        $pAccountType->setPlaceholder('{USER.ACCOUNT_TYPE}');
        $manager->persist($pAccountType);
        $this->addReference('placeholder-accountType', $pAccountType);

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

        $pDateNewEnd = new SitePlaceholder();
        $pDateNewEnd->setName('Ende der Verlängerung (Datum)');
        $pDateNewEnd->setPlaceholder('{DATE.NEW_END}');
        $manager->persist($pDateNewEnd);
        $this->addReference('placeholder-dateNewEnd', $pDateNewEnd);

        $pDeviceName = new SitePlaceholder();
        $pDeviceName->setName('Gerät');
        $pDeviceName->setPlaceholder('{DEVICE.NAME}');
        $manager->persist($pDeviceName);
        $this->addReference('placeholder-deviceName', $pDeviceName);

        $pSerialNumber = new SitePlaceholder();
        $pSerialNumber->setName('Seriennummer');
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
