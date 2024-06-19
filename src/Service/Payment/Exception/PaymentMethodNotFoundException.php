<?php

namespace App\Service\Payment\Exception;

use App\Service\Payment\PaymentMethod;

final class PaymentMethodNotFoundException extends PaymentException
{
    public function __construct(PaymentMethod $paymentMethod, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct('Payment method "'.$paymentMethod->name.'" not found');
    }
}
