<?php

namespace App\Service\Payment\Coupon;

enum Type: int
{
    case FIXED = 1;
    case PERCENTAGE = 2;
}
