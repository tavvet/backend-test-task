<?php

namespace App\Service\Payment\Gateway;

use App\Service\Payment\Exception\PaymentException;
use App\Service\Payment\PaymentMethod;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

final readonly class StripeGateway implements PaymentGatewayInterface
{
    public function __construct(
        private StripePaymentProcessor $processor,
    ) {
    }

    public function pay(float $amount): void
    {
        if ($amount < 0.01) {
            throw new PaymentException('Invalid amount '.$amount);
        }

        if (!$this->processor->processPayment($amount)) {
            throw new PaymentException('Failed to pay payment');
        }
    }

    public function support(PaymentMethod $method): bool
    {
        return PaymentMethod::STRIPE === $method;
    }
}
