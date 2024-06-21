<?php

namespace App\Tests\Service\Payment;

use App\Service\Payment\Exception\PaymentException;
use App\Service\Payment\PaymentMethod;
use App\Service\Payment\PaymentProcessor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PaymentProcessorTest extends KernelTestCase
{
    private ?PaymentProcessor $paymentProcessor;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->paymentProcessor = self::getContainer()->get(PaymentProcessor::class);
    }

    public function testPayPaypal(): void
    {
        $this->assertNull($this->paymentProcessor->pay(PaymentMethod::PAYPAL, 1000));
        $this->expectException(PaymentException::class);
        $this->paymentProcessor->pay(PaymentMethod::PAYPAL, 100001);
    }

    public function testPayStripe(): void
    {
        $this->assertNull($this->paymentProcessor->pay(PaymentMethod::STRIPE, 1000));
        $this->expectException(PaymentException::class);
        $this->paymentProcessor->pay(PaymentMethod::STRIPE, 1);
    }

    public function testPayZero(): void
    {
        $this->assertNull($this->paymentProcessor->pay(PaymentMethod::STRIPE, 0));
    }
}
