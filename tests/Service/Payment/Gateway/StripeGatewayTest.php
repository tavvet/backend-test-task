<?php

namespace App\Tests\Service\Payment\Gateway;

use App\Service\Payment\Exception\PaymentException;
use App\Service\Payment\Gateway\StripeGateway;
use App\Service\Payment\PaymentMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class StripeGatewayTest extends KernelTestCase
{
    private ?StripeGateway $gateway;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->gateway = self::getContainer()->get(StripeGateway::class);
    }

    public function testSupport(): void
    {
        $this->assertTrue($this->gateway->support(PaymentMethod::STRIPE));
        $this->assertFalse($this->gateway->support(PaymentMethod::PAYPAL));
    }

    public function testPayValid(): void
    {
        $this->assertNull($this->gateway->pay(100));
        $this->assertNull($this->gateway->pay(100000));
    }

    public function testPayLessThanMinAmount(): void
    {
        $this->expectException(PaymentException::class);
        $this->gateway->pay(99.99);
    }
}
