<?php

namespace App\Tests\Service\Payment\Gateway;

use App\Service\Payment\Exception\PaymentException;
use App\Service\Payment\Gateway\PaypalGateway;
use App\Service\Payment\PaymentMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PaypalGatewayTest extends KernelTestCase
{
    private ?PaypalGateway $gateway;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->gateway = self::getContainer()->get(PaypalGateway::class);
    }

    public function testSupport(): void
    {
        $this->assertTrue($this->gateway->support(PaymentMethod::PAYPAL));
        $this->assertFalse($this->gateway->support(PaymentMethod::STRIPE));
    }

    public function testPayValid(): void
    {
        $this->assertNull($this->gateway->pay(100000));
        $this->assertNull($this->gateway->pay(0.01));
    }

    public function testPayNegative(): void
    {
        $this->expectException(PaymentException::class);
        $this->gateway->pay(-1);
    }

    public function testPayZero(): void
    {
        $this->expectException(PaymentException::class);
        $this->gateway->pay(0);
    }

    public function testPayGreaterThanMaxAmount(): void
    {
        $this->expectException(PaymentException::class);
        $this->gateway->pay(100000.0001);
    }
}
