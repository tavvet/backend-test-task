<?php

namespace App\Tests\Repository;

use App\Entity\Country;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CountryRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testFindByTaxNumberExists(): void
    {
        /** @var CountryRepository $repository */
        $repository = $this->entityManager->getRepository(Country::class);

        $germany = $repository->findByTaxNumber('DEabcdefghi');
        $this->assertInstanceOf(Country::class, $germany);
        $this->assertEquals('Germany', $germany->getName());
    }

    public function testFindByTaxNumberNotExists(): void
    {
        /** @var CountryRepository $repository */
        $repository = $this->entityManager->getRepository(Country::class);

        // Invalid prefix
        $this->assertNull($repository->findByTaxNumber('Eabcdefghs'));
        // Invalid postfix
        $this->assertNull($repository->findByTaxNumber('DEabcdefgh'));
    }
}
