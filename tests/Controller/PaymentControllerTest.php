<?php

namespace App\Tests\Controller;

use App\Entity\Product;
use App\Repository\CountryRepository;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;
use App\Service\Api\Exception\ApiException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class PaymentControllerTest extends WebTestCase
{
    public function testCalculatePriceActionValid(): void
    {
        $client = self::createClient();

        $product = (new Product())
            ->setName('iPhone')
            ->setPrice(100)
        ;

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects(self::once())
            ->method('find')
            ->willReturn($product)
        ;

        self::getContainer()->set(ProductRepository::class, $productRepository);

        $client->request(
            Request::METHOD_POST,
            '/calculate-price',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 1,
                'taxNumber' => 'DEasdzxcqwe',
            ]),
        );

        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertSame(
            $response->getContent(),
            json_encode(['code' => Response::HTTP_OK, 'price' => 119])
        );
    }

    public function testCalculatePriceActionWithNotExistsProduct(): void
    {
        $client = self::createClient();

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects(self::once())
            ->method('find')
            ->willReturn(null)
        ;

        self::getContainer()->set(ProductRepository::class, $productRepository);

        $client->request(
            Request::METHOD_POST,
            '/calculate-price',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 1,
                'taxNumber' => 'DEasdzxcqwe',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCalculatePriceActionWithInvalidRequestData(): void
    {
        $client = self::createClient();

        $client->request(
            Request::METHOD_POST,
            '/calculate-price',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '',
        );

        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertSame(
            $response->getContent(),
            json_encode(['error' => 'Invalid data', 'code' => Response::HTTP_BAD_REQUEST]),
        );
    }

    public function testCalculatePriceActionWithNotExistsCoupon(): void
    {
        $client = self::createClient();

        $product = (new Product())
            ->setName('iPhone')
            ->setPrice(80)
        ;

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects(self::once())
            ->method('find')
            ->willReturn($product)
        ;

        $couponRepository = $this->createMock(CouponRepository::class);
        $couponRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn(null)
        ;

        self::getContainer()->set(ProductRepository::class, $productRepository);
        self::getContainer()->set(CouponRepository::class, $couponRepository);

        $client->request(
            Request::METHOD_POST,
            '/calculate-price',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 1,
                'taxNumber' => 'DEasdzxcqwe',
                'couponCode' => 'D20',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCalculatePriceActionWithNotExistsCountry(): void
    {
        $client = self::createClient();

        $product = (new Product())
            ->setName('iPhone')
            ->setPrice(80)
        ;

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects(self::once())
            ->method('find')
            ->willReturn($product)
        ;

        $countryRepository = $this->createMock(CountryRepository::class);
        $countryRepository->expects(self::once())
            ->method('findByTaxNumber')
            ->willReturn(null)
        ;

        self::getContainer()->set(ProductRepository::class, $productRepository);
        self::getContainer()->set(CountryRepository::class, $countryRepository);

        $client->request(
            Request::METHOD_POST,
            '/calculate-price',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 1,
                'taxNumber' => 'DEasdzxcqwe',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCalculatePriceActionWithInvalidProductId(): void
    {
        $client = self::createClient();

        $client->request(
            Request::METHOD_POST,
            '/calculate-price',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 'asd',
                'taxNumber' => 'DEasdzxcqwe',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testCalculatePriceActionWithNegativePrice(): void
    {
        $client = self::createClient();

        $product = (new Product())
            ->setName('iPhone')
            ->setPrice(-10)
        ;

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects(self::once())
            ->method('find')
            ->willReturn($product)
        ;

        self::getContainer()->set(ProductRepository::class, $productRepository);

        $client->request(
            Request::METHOD_POST,
            '/calculate-price',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 1,
                'taxNumber' => 'DEasdzxcqwe',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testCalculatePriceActionWithRateLimit(): void
    {
        $client = static::createClient();

        $limit = $this->createMock(RateLimit::class);
        $limit->expects($this->once())
            ->method('isAccepted')
            ->willReturn(false)
        ;

        $limiter = $this->createMock(LimiterInterface::class);
        $limiter->expects($this->once())
            ->method('consume')
            ->willReturn($limit)
        ;

        $factory = $this->createMock(RateLimiterFactory::class);
        $factory->expects(self::once())
            ->method('create')
            ->willReturn($limiter)
        ;

        self::getContainer()->set('limiter.anonymous_api', $factory);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(Response::HTTP_TOO_MANY_REQUESTS);

        $client->request(
            Request::METHOD_POST,
            '/calculate-price',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 1,
                'taxNumber' => 'DEasdzxcqwe',
            ]),
        );
    }

    public function testPurchaseActionWithInvalidAmountStripe(): void
    {
        $client = self::createClient();

        $product = (new Product())
            ->setName('iPhone')
            ->setPrice(80)
        ;

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects(self::once())
            ->method('find')
            ->willReturn($product)
        ;

        self::getContainer()->set(ProductRepository::class, $productRepository);

        $client->request(
            Request::METHOD_POST,
            '/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 10,
                'taxNumber' => 'DEasdzxcqwe',
                'paymentProcessor' => 'stripe',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testPurchaseActionWithValidAmountStripe(): void
    {
        $client = self::createClient();

        $product = (new Product())
            ->setName('iPhone')
            ->setPrice(100)
        ;

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects(self::once())
            ->method('find')
            ->willReturn($product)
        ;

        self::getContainer()->set(ProductRepository::class, $productRepository);

        $client->request(
            Request::METHOD_POST,
            '/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 10,
                'taxNumber' => 'DEasdzxcqwe',
                'paymentProcessor' => 'stripe',
            ]),
        );

        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertSame(
            $response->getContent(),
            json_encode(['code' => Response::HTTP_OK, 'success' => true]),
        );
    }

    public function testPurchaseActionWithInvalidAmountPaypal(): void
    {
        $client = self::createClient();

        $product = (new Product())
            ->setName('iPhone')
            ->setPrice(1000000.01)
        ;

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects(self::once())
            ->method('find')
            ->willReturn($product)
        ;

        self::getContainer()->set(ProductRepository::class, $productRepository);

        $client->request(
            Request::METHOD_POST,
            '/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 10,
                'taxNumber' => 'DEasdzxcqwe',
                'paymentProcessor' => 'paypal',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testPurchaseActionWithValidAmountPaypal(): void
    {
        $client = self::createClient();

        $product = (new Product())
            ->setName('iPhone')
            ->setPrice(100)
        ;

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects(self::once())
            ->method('find')
            ->willReturn($product)
        ;

        self::getContainer()->set(ProductRepository::class, $productRepository);

        $client->request(
            Request::METHOD_POST,
            '/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 10,
                'taxNumber' => 'DEasdzxcqwe',
                'paymentProcessor' => 'paypal',
            ]),
        );

        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertSame(
            $response->getContent(),
            json_encode(['code' => Response::HTTP_OK ,'success' => true]),
        );
    }

    public function testPurchaseActionWithInvalidRequestData(): void
    {
        $client = self::createClient();

        $client->request(
            Request::METHOD_POST,
            '/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
           'asd',
        );

        $response = $client->getResponse();

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertSame(
            $response->getContent(),
            json_encode(['error' => 'Invalid data', 'code' => Response::HTTP_BAD_REQUEST]),
        );
    }

    public function testPurchaseActionWithInvalidTaxNumber(): void
    {
        $client = self::createClient();

        $client->request(
            Request::METHOD_POST,
            '/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 10,
                'taxNumber' => 'Dasdzxcqwe',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testPurchaseActionWithInvalidCoupon(): void
    {
        $client = self::createClient();

        $client->request(
            Request::METHOD_POST,
            '/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 10,
                'taxNumber' => 'DEasdzxcqwe',
                'couponCode' => 'S203',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testPurchaseActionWithInvalidProductId(): void
    {
        $client = self::createClient();

        $client->request(
            Request::METHOD_POST,
            '/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 'asd',
                'taxNumber' => 'DEasdzxcqwe',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testPurchaseActionWithNotExistsProduct(): void
    {
        $client = self::createClient();
        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects(self::once())
            ->method('find')
            ->willReturn(null)
        ;

        self::getContainer()->set(ProductRepository::class, $productRepository);

        $client->request(
            Request::METHOD_POST,
            '/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 10,
                'taxNumber' => 'DEasdzxcqwe',
                'paymentProcessor' => 'paypal',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testPurchaseActionWithNotExistsCoupon(): void
    {
        $client = self::createClient();

        $product = (new Product())
            ->setName('iPhone')
            ->setPrice(100)
        ;

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects(self::once())
            ->method('find')
            ->willReturn($product)
        ;

        $couponRepository = $this->createMock(CouponRepository::class);
        $couponRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn(null)
        ;

        self::getContainer()->set(ProductRepository::class, $productRepository);
        self::getContainer()->set(CouponRepository::class, $couponRepository);

        $client->request(
            Request::METHOD_POST,
            '/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 10,
                'taxNumber' => 'DEasdzxcqwe',
                'couponCode' => 'D20',
                'paymentProcessor' => 'paypal',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testPurchaseActionWithNotExistsCountry(): void
    {
        $client = self::createClient();

        $product = (new Product())
            ->setName('iPhone')
            ->setPrice(100)
        ;

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects(self::once())
            ->method('find')
            ->willReturn($product)
        ;

        $countryRepository = $this->createMock(CountryRepository::class);
        $countryRepository->expects(self::once())
            ->method('findByTaxNumber')
            ->willReturn(null)
        ;

        self::getContainer()->set(ProductRepository::class, $productRepository);
        self::getContainer()->set(CountryRepository::class, $countryRepository);

        $client->request(
            Request::METHOD_POST,
            '/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 10,
                'taxNumber' => 'DEasdzxcqwe',
                'paymentProcessor' => 'paypal',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testPurchaseActionWithNegativePrice(): void
    {
        $client = self::createClient();

        $product = (new Product())
            ->setName('iPhone')
            ->setPrice(-1)
        ;

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects(self::once())
            ->method('find')
            ->willReturn($product)
        ;

        self::getContainer()->set(ProductRepository::class, $productRepository);

        $client->request(
            Request::METHOD_POST,
            '/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 10,
                'taxNumber' => 'DEasdzxcqwe',
                'paymentProcessor' => 'paypal',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testPurchaseActionWithRateLimit(): void
    {
        $client = static::createClient();

        $limit = $this->createMock(RateLimit::class);
        $limit->expects($this->once())
            ->method('isAccepted')
            ->willReturn(false)
        ;

        $limiter = $this->createMock(LimiterInterface::class);
        $limiter->expects($this->once())
            ->method('consume')
            ->willReturn($limit)
        ;

        $factory = $this->createMock(RateLimiterFactory::class);
        $factory->expects(self::once())
            ->method('create')
            ->willReturn($limiter)
        ;

        self::getContainer()->set('limiter.anonymous_api', $factory);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(Response::HTTP_TOO_MANY_REQUESTS);

        $client->request(
            Request::METHOD_POST,
            '/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 1,
                'taxNumber' => 'DEasdzxcqwe',
                'paymentProcessor' => 'paypal',
            ]),
        );
    }
}
