<?php

namespace App\Http\Argument;

use App\Entity\Country;
use App\Entity\Coupon;
use App\Entity\Product;

final readonly class PriceCalculationData
{
    public function __construct(
        public Product $product,
        public Country $country,
        public ?Coupon $coupon = null,
    ) {
    }
}
