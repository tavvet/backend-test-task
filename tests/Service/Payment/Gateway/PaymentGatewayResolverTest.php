<?php

namespace App\Tests\Service\Payment\Gateway;

use App\Service\Payment\Gateway\PaymentGatewayInterface;
use App\Service\Payment\Gateway\PaymentGatewayResolver;
use App\Service\Payment\PaymentMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PaymentGatewayResolverTest extends KernelTestCase
{
    private ?PaymentGatewayResolver $resolver;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->resolver = self::getContainer()->get(PaymentGatewayResolver::class);
    }

    public function testResolvePaypal(): void
    {
        $this->assertInstanceOf(
            PaymentGatewayInterface::class,
            $this->resolver->resolve(PaymentMethod::PAYPAL),
        );
    }

    public function testResolveStripe(): void
    {
        $this->assertInstanceOf(
            PaymentGatewayInterface::class,
            $this->resolver->resolve(PaymentMethod::STRIPE),
        );
    }
}
