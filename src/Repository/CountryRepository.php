<?php

namespace App\Repository;

use App\Entity\Country;
use App\Service\Payment\TaxNumberConverter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Country>
 */
class CountryRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly TaxNumberConverter $taxNumberConverter,
    ) {
        parent::__construct($registry, Country::class);
    }

    public function findByTaxNumber(string $taxNumber): ?Country
    {
        $taxNumberFormat = $this->taxNumberConverter->toTaxNumberFormat($taxNumber);

        return $this->createQueryBuilder('country')
            ->where('country.taxNumberFormat = :taxNumberFormat')
            ->setParameter('taxNumberFormat', $taxNumberFormat)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
