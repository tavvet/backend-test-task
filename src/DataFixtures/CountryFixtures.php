<?php

namespace App\DataFixtures;

use App\Entity\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CountryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist(
            (new Country())
                ->setName('Germany')
                ->setTaxRate(19)
                ->setTaxNumberFormat('DEXXXXXXXXX')
        );

        $manager->persist(
            (new Country())
                ->setName('Italy')
                ->setTaxRate(22)
                ->setTaxNumberFormat('ITXXXXXXXXXXX')
        );

        $manager->persist(
            (new Country())
                ->setName('France')
                ->setTaxRate(20)
                ->setTaxNumberFormat('FRYYXXXXXXXXX')
        );

        $manager->persist(
            (new Country())
                ->setName('Greece')
                ->setTaxRate(24)
                ->setTaxNumberFormat('GRXXXXXXXXX')
        );

        $manager->flush();
    }
}
