<?php

namespace UniHalle\RentBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use UniHalle\RentBundle\Entity\Site;
use UniHalle\RentBundle\Types\SiteType;

class LoadSites extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $sites = array();

        $index = new Site();
        $index->setName('Startseite');
        $index->setIdentifier('index');
        $index->setType(SiteType::CONTENT);
        $sites[] = $index;

        $indexContact = new Site();
        $indexContact->setName('Startseite: Kontaktdaten');
        $indexContact->setIdentifier('indexContact');
        $indexContact->setType(SiteType::CONTENT);
        $sites[] = $indexContact;

        $docRent = new Site();
        $docRent->setName('Dokument: Leihschein');
        $docRent->setIdentifier('docRent');
        $docRent->setType(SiteType::DOCUMENT);
        $this->addDocumentPlaceholders($docRent);
        $sites[] = $docRent;

        $docRentBack = new Site();
        $docRentBack->setName('Dokument: Rückgabeschein');
        $docRentBack->setIdentifier('docRentBack');
        $docRentBack->setType(SiteType::DOCUMENT);
        $this->addDocumentPlaceholders($docRentBack);
        $sites[] = $docRentBack;

        $mailRegAccepted = new Site();
        $mailRegAccepted->setName('Nutzer: Freischaltung');
        $mailRegAccepted->setIdentifier('mailRegAccepted');
        $mailRegAccepted->setType(SiteType::USER_MAIL);
        $this->addRegisterMailPlaceholders($mailRegAccepted);
        $sites[] = $mailRegAccepted;

        $mailRegDenied = new Site();
        $mailRegDenied->setName('Nutzer: Registrierung abgelehnt');
        $mailRegDenied->setIdentifier('mailRegDenied');
        $mailRegDenied->setType(SiteType::USER_MAIL);
        $this->addRegisterMailPlaceholders($mailRegDenied);
        $sites[] = $mailRegDenied;

        $mailRentalAccepted = new Site();
        $mailRentalAccepted->setName('Buchung bestätigt');
        $mailRentalAccepted->setIdentifier('mailRentalAccpeted');
        $mailRentalAccepted->setType(SiteType::USER_MAIL);
        $this->addRentMailPlaceholders($mailRentalAccepted);
        $sites[] = $mailRentalAccepted;

        $mailRentalDenied = new Site();
        $mailRentalDenied->setName('Buchung abgelehnt');
        $mailRentalDenied->setIdentifier('mailRentalDenied');
        $mailRentalDenied->setType(SiteType::USER_MAIL);
        $this->addRentMailPlaceholders($mailRentalDenied);
        $sites[] = $mailRentalDenied;

        $mailRentalExtendAccepted = new Site();
        $mailRentalExtendAccepted->setName('Buchungsverlängerung bestätigt');
        $mailRentalExtendAccepted->setIdentifier('mailRentalExtendAccepted');
        $mailRentalExtendAccepted->setType(SiteType::USER_MAIL);
        $this->addRentMailPlaceholders($mailRentalExtendAccepted);
        $mailRentalExtendAccepted->getPlaceholders()->add($this->getReference('placeholder-dateNewEnd'));
        $sites[] = $mailRentalExtendAccepted;

        $mailRentalExtendDenied = new Site();
        $mailRentalExtendDenied->setName('Buchungsverlängerung abgelehnt');
        $mailRentalExtendDenied->setIdentifier('mailRentalExtendDenied');
        $mailRentalExtendDenied->setType(SiteType::USER_MAIL);
        $this->addRentMailPlaceholders($mailRentalExtendDenied);
        $mailRentalExtendDenied->getPlaceholders()->add($this->getReference('placeholder-dateNewEnd'));
        $sites[] = $mailRentalExtendDenied;

        $mailRentalUpdated = new Site();
        $mailRentalUpdated->setName('Buchungsdaten geändert');
        $mailRentalUpdated->setIdentifier('mailRentalUpdated');
        $mailRentalUpdated->setType(SiteType::USER_MAIL);
        $this->addRentMailPlaceholders($mailRentalUpdated);
        $sites[] = $mailRentalUpdated;

        $mailAdminNewUser = new Site();
        $mailAdminNewUser->setName('Nutzer: Registrierung');
        $mailAdminNewUser->setIdentifier('mailUserRegistered');
        $mailAdminNewUser->setType(SiteType::ADMIN_MAIL);
        $mailAdminNewUser->setSubject('Geräteverleih: Nutzerregistrierung');
        $mailAdminNewUser->setContent("Nutzerregistrierung\nVorname: {USER.NAME}\nNachname: {USER.SURNAME}\nNutzerkennzeichen: {USER.NKZ}\nAccount-Typ: {USER.ACCOUNT_TYPE}");
        $this->addRegisterMailPlaceholders($mailAdminNewUser);
        $sites[] = $mailAdminNewUser;

        $mailAdminNewRent = new Site();
        $mailAdminNewRent->setName('Neue Buchung');
        $mailAdminNewRent->setIdentifier('mailNewRent');
        $mailAdminNewRent->setType(SiteType::ADMIN_MAIL);
        $this->addRentMailPlaceholders($mailAdminNewRent);
        $sites[] = $mailAdminNewRent;

        $mailAdminExtendRent = new Site();
        $mailAdminExtendRent->setName('Buchungsverlängerung');
        $mailAdminExtendRent->setIdentifier('mailExtendRent');
        $mailAdminExtendRent->setType(SiteType::ADMIN_MAIL);
        $this->addRentMailPlaceholders($mailAdminExtendRent);
        $mailAdminExtendRent->getPlaceholders()->add($this->getReference('placeholder-dateNewEnd'));
        $sites[] = $mailAdminExtendRent;

        foreach ($sites as $site) {
            $manager->persist($site);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }

    private function addDocumentPlaceholders($document)
    {
        $document->getPlaceholders()->add($this->getReference('placeholder-surname'));
        $document->getPlaceholders()->add($this->getReference('placeholder-name'));
        $document->getPlaceholders()->add($this->getReference('placeholder-mail'));
        $document->getPlaceholders()->add($this->getReference('placeholder-dateNow'));
        $document->getPlaceholders()->add($this->getReference('placeholder-dateStart'));
        $document->getPlaceholders()->add($this->getReference('placeholder-dateEnd'));
        $document->getPlaceholders()->add($this->getReference('placeholder-deviceName'));
        $document->getPlaceholders()->add($this->getReference('placeholder-serialNumber'));
    }

    private function addRentMailPlaceholders($mail)
    {
        $this->addDocumentPlaceholders($mail);
    }

    private function addRegisterMailPlaceholders($mail)
    {
        $mail->getPlaceholders()->add($this->getReference('placeholder-surname'));
        $mail->getPlaceholders()->add($this->getReference('placeholder-name'));
        $mail->getPlaceholders()->add($this->getReference('placeholder-mail'));
        $mail->getPlaceholders()->add($this->getReference('placeholder-uid'));
        $mail->getPlaceholders()->add($this->getReference('placeholder-accountType'));
    }
}
