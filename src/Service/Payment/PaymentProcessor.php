<?php

namespace App\Service\Payment;

use App\Service\Payment\Exception\PaymentException;
use App\Service\Payment\Gateway\PaymentGatewayResolver;

final readonly class PaymentProcessor
{
    public function __construct(
        private PaymentGatewayResolver $gatewayResolver,
    ) {
    }

    /**
     * @throws PaymentException
     */
    public function pay(PaymentMethod $paymentMethod, float $amount): void
    {
        if (0 == $amount) {
            return;
        }

        if ($amount < 0) {
            throw new PaymentException('Invalid amount '.$amount);
        }

        $gateway = $this->gatewayResolver->resolve($paymentMethod);
        $gateway->pay($amount);
    }
}
