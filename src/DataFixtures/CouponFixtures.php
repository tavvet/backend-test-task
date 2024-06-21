<?php

namespace App\DataFixtures;

use App\Entity\Coupon;
use App\Service\Payment\Coupon\Type as CouponType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CouponFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (range(5, 20, 5) as $percent) {
            $manager->persist(
                (new Coupon())
                    ->setCode('P'.$percent)
                    ->setType(CouponType::PERCENTAGE)
                    ->setValue($percent)
            );
        }

        foreach (range(5, 20, 5) as $fixedDiscount) {
            $manager->persist(
                (new Coupon())
                    ->setCode('D'.$fixedDiscount)
                    ->setType(CouponType::FIXED)
                    ->setValue($fixedDiscount)
            );
        }

        $manager->flush();
    }
}
