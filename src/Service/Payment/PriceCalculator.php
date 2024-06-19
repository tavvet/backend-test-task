<?php

namespace App\Service\Payment;

use App\Service\Payment\Exception\InvalidArgumentException;

final class PriceCalculator
{
    public function calculatePrice(float $price, int $taxRate, int $discount = 0, bool $discountIsPercent = false): float
    {
        if ($price < 0) {
            throw new InvalidArgumentException('Invalid price "'.$price.'"');
        }

        return $this->applyTaxRate(
            $this->applyDiscount($price, $discount, $discountIsPercent),
            $taxRate,
        );
    }

    private function applyTaxRate(float $price, float $taxRate): float
    {
        $price *= (100 + $taxRate) / 100;

        return $this->roundPrice($price);
    }

    private function applyDiscount(float $price, int $discount, bool $discountIsPercent): float
    {
        if ($discountIsPercent) {
            $price *= (100 - $discount) / 100;
            $price = $this->roundPrice($price);
        } else {
            $price -= $discount;
            if ($price < 0) {
                $price = 0;
            }
        }

        return $price;
    }

    private function roundPrice(float $price): float
    {
        $reg = $price + 0.5 / 100;

        return round($reg, 2, $reg > 0 ? PHP_ROUND_HALF_DOWN : PHP_ROUND_HALF_UP);
    }
}
