<?php

namespace App\Tests\Service\Payment;

use App\Service\Payment\TaxNumberConverter;
use PHPUnit\Framework\TestCase;

final class TaxNumberConverterTest extends TestCase
{
    private ?TaxNumberConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new TaxNumberConverter();
    }

    public function testToTaxNumberFormat()
    {
        $this->assertEquals(
            'DEXXX',
            $this->converter->toTaxNumberFormat('DEasd')
        );

        $this->assertEquals(
            'DEYYY',
            $this->converter->toTaxNumberFormat('DE123')
        );

        $this->assertEquals(
            'DEXXYYY',
            $this->converter->toTaxNumberFormat('DEAs123')
        );
    }
}
