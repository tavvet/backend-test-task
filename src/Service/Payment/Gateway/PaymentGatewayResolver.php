<?php

namespace App\Service\Payment\Gateway;

use App\Service\Payment\Exception\PaymentMethodNotFoundException;
use App\Service\Payment\PaymentMethod;

final readonly class PaymentGatewayResolver
{
    public function __construct(
        /** @var iterable|PaymentGatewayInterface[] */
        private iterable $gateways,
    ) {
    }

    /**
     * @throws PaymentMethodNotFoundException
     */
    public function resolve(PaymentMethod $method): PaymentGatewayInterface
    {
        foreach ($this->gateways as $adapter) {
            if ($adapter->support($method)) {
                return $adapter;
            }
        }

        throw new PaymentMethodNotFoundException($method);
    }
}
