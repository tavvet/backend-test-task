<?php

namespace App\Repository;

use App\Entity\Country;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Country>
 */
class CountryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Country::class);
    }

    public function findByTaxNumber(string $taxNumber): ?Country
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

        return $this->createQueryBuilder('country')
            ->where('country.taxNumberFormat = :taxNumberFormat')
            ->setParameter('taxNumberFormat', $taxNumberFormat)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
