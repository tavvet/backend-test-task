<?php

namespace App\Http\Argument;

use App\Entity\Country;
use App\Entity\Coupon;
use App\Entity\Product;
use App\Service\Payment\PaymentMethod;

final readonly class PurchaseData
{
    public function __construct(
        public Product $product,
        public Country $country,
        public PaymentMethod $paymentMethod,
        public ?Coupon $coupon = null,
    ) {
    }
}
