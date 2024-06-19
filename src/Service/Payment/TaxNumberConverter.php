<?php

namespace App\Service\Payment;

final class TaxNumberConverter
{
    public function toTaxNumberFormat(string $taxNumber): string
    {
        $taxNumberFormat =
            substr($taxNumber, 0, 2)
            .preg_replace(
                '/[0-9]{1}/',
                'Y',
                preg_replace(
                    '/[a-zA-Z]{1}/',
                    'X',
                    substr($taxNumber, 2)
                )
            )
        ;

        return $taxNumberFormat;
    }
}
