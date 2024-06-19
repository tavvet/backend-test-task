<?php

namespace App\Service\Payment\Gateway;

use App\Service\Payment\Exception\PaymentException;
use App\Service\Payment\PaymentMethod;

interface PaymentGatewayInterface
{
    /**
     * @throws PaymentException
     */
    public function pay(float $amount): void;

    public function support(PaymentMethod $method): bool;
}
