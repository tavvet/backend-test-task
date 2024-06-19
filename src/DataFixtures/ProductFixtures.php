<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist(
            (new Product())
                ->setName('iPhone')
                ->setPrice(100.)
        );

        $manager->persist(
            (new Product())
                ->setName('AirPods')
                ->setPrice(20.)
        );

        $manager->persist(
            (new Product())
                ->setName('Case')
                ->setPrice(10.)
        );

        $manager->flush();
    }
}
