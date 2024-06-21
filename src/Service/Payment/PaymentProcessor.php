<?php

namespace App\Service\Payment;

use App\Service\Payment\Gateway\PaymentGatewayResolver;

final readonly class PaymentProcessor
{
    public function __construct(
        private PaymentGatewayResolver $gatewayResolver,
    ) {
    }

    /**
     * @throws Exception\PaymentException
     */
    public function pay(PaymentMethod $paymentMethod, float $amount): void
    {
        if ($amount == 0) {
            return;
        }

        $gateway = $this->gatewayResolver->resolve($paymentMethod);
        $gateway->pay($amount);
    }
}
