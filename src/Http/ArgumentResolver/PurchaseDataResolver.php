<?php

namespace App\Http\ArgumentResolver;

use App\Http\Argument\PurchaseData;
use App\Repository\CountryRepository;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;
use App\Service\Api\Exception\ApiException;
use App\Service\Payment\PaymentMethod;
use App\Validator\Constraints as AppAssert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class PurchaseDataResolver implements ValueResolverInterface
{
    public function __construct(
        private ValidatorInterface $validator,
        private ProductRepository $productRepository,
        private CouponRepository $couponRepository,
        private CountryRepository $countryRepository,
    ) {
    }

    /**
     * @throws ApiException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (PurchaseData::class !== $argument->getType()) {
            return [];
        }

        $content = $request->getContent();

        if (!json_validate($content)) {
            throw new ApiException('Invalid data', Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($content, true);

        $errors = $this->validator->validate(
            $data,
            new Assert\Collection([
                'product' => new Assert\Required([
                    new Assert\NotBlank(allowNull: false),
                    new Assert\Type('int'),
                    new Assert\Range(min: 0),
                ]),
                'taxNumber' => new Assert\Required([
                    new Assert\NotBlank(allowNull: false),
                    new Assert\Type('string'),
                    new Assert\Regex('/[A-Z]{2}[a-zA-Z0-9]+/'),
                ]),
                'couponCode' => [
                    new AppAssert\Optional([
                        new Assert\NotBlank(allowNull: false),
                        new Assert\Type('string'),
                        new Assert\Regex('/[PD]{1}\d+/'),
                    ]),
                ],
                'paymentProcessor' => new Assert\Required([
                    new Assert\NotBlank(allowNull: false),
                    new Assert\Choice(
                        array_map(
                            fn (PaymentMethod $method) => $method->value,
                            PaymentMethod::cases(),
                        ),
                    ),
                ]),
            ])
        );

        if ($errors->count() > 0) {
            /** @var ConstraintViolation $error */
            $error = $errors[0];
            throw new ApiException("{$error->getPropertyPath()}: {$error->getMessage()}", Response::HTTP_BAD_REQUEST);
        }

        $product = $this->productRepository->find($data['product']);

        if (null === $product) {
            throw new ApiException('Product not found', Response::HTTP_NOT_FOUND);
        }

        $coupon = null;
        if (array_key_exists('couponCode', $data)) {
            $coupon = $this->couponRepository->findOneBy(['code' => $data['couponCode']]);
            if (null === $coupon) {
                throw new ApiException('Coupon not found', Response::HTTP_NOT_FOUND);
            }
        }

        $country = $this->countryRepository->findByTaxNumber($data['taxNumber']);
        if (null === $country) {
            throw new ApiException('Country not found by taxNumber', Response::HTTP_NOT_FOUND);
        }

        return [
            new PurchaseData(
                $product,
                $country,
                PaymentMethod::from($data['paymentProcessor']),
                $coupon,
            ),
        ];
    }
}
