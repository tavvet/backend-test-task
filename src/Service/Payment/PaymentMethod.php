<?php

namespace App\Service\Payment;

enum PaymentMethod: string
{
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';
}
