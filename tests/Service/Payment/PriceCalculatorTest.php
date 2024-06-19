<?php

namespace App\Tests\Service\Payment;

use App\Service\Payment\Exception\InvalidArgumentException;
use App\Service\Payment\PriceCalculator;
use PHPUnit\Framework\TestCase;

final class PriceCalculatorTest extends TestCase
{
    private ?PriceCalculator $priceCalculator;

    protected function setUp(): void
    {
        $this->priceCalculator = new PriceCalculator();
    }

    public function testCalculatePriceWithoutTaxRateAndWithoutDiscount(): void
    {
        $this->assertEquals(
            100,
            $this->priceCalculator->calculatePrice(100, 0, 0),
        );
    }

    public function testCalculatePriceWithTaxRateAndWithoutDiscount(): void
    {
        $this->assertEquals(
            110,
            $this->priceCalculator->calculatePrice(100, 10, 0),
        );
    }

    public function testCalculatePriceWithoutTaxRateAndWithFixedDiscount(): void
    {
        $this->assertEquals(
            90,
            $this->priceCalculator->calculatePrice(100, 0, 10),
        );
    }

    public function testCalculatePriceWithoutTaxRateAndWithPercentDiscount(): void
    {
        $this->assertEquals(
            95,
            $this->priceCalculator->calculatePrice(100, 0, 5, true),
        );
    }

    public function testCalculatePriceWithTaxRateAndWithFixedDiscount(): void
    {
        $this->assertEquals(
            99,
            $this->priceCalculator->calculatePrice(100, 10, 10, false),
        );
    }

    public function testCalculatePriceWithTaxRateAndWithPercentDiscount(): void
    {
        $this->assertEquals(
            209,
            $this->priceCalculator->calculatePrice(200, 10, 5, true),
        );
    }

    public function testCalculatePriceWithDiscountGreaterThanPrice(): void
    {
        $this->assertEquals(
            0,
            $this->priceCalculator->calculatePrice(200, 10, 300),
        );
    }

    public function testCalculatePriceWithNegativePrice(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->priceCalculator->calculatePrice(-1000, 0);
    }
}
