<?php

namespace App\Service\Payment\Gateway;

use App\Service\Payment\Exception\PaymentException;
use App\Service\Payment\PaymentMethod;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;

final readonly class PaypalGateway implements PaymentGatewayInterface
{
    public function __construct(
        private PaypalPaymentProcessor $processor,
    ) {
    }

    public function pay(float $amount): void
    {
        try {
            $this->processor->pay(ceil($amount));
        } catch (\Throwable $throwable) {
            throw new PaymentException($throwable->getMessage());
        }
    }

    public function support(PaymentMethod $method): bool
    {
        return PaymentMethod::PAYPAL === $method;
    }
}
