<?php

namespace UniHalle\RentBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ConfigurationRepository extends EntityRepository
{
    public function getValue($identifier)
    {
        $result = $this->findOneByIdentifier($identifier);
        return ($result) ? $result->getValue() : '';
    }

    public function getHolidays()
    {
        $holidays = array();
        $result = $this->findOneByIdentifier('holidays');
        if (!$result) {
            return $holidays;
        }
        $value = $result->getValue();
        str_replace("\r\n", "\n", $value);
        $values = explode("\n", $value);
        foreach ($values as $v) {
            $v = trim($v);
            if ($v == '') {
                continue;
            }

            $parts = explode('.', $v);
            if (count($parts) != 3) {
                continue;
            }
            if (strlen($parts[2]) == 2) {
                $parts[2] = '20'.$parts[2];
            }

            $holidays[] = new \DateTime($parts[2].'-'.$parts[1].'-'.$parts[0].' 00:00:00');
        }

        return $holidays;
    }
}
