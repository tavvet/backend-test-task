<?php

namespace App\Controller;

use App\Http\Argument\PriceCalculationData;
use App\Http\ArgumentResolver\PriceCalculationDataResolver;
use App\Service\Api\Exception\ApiException;
use App\Service\Payment\Coupon\Type as CouponType;
use App\Service\Payment\PriceCalculator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

final class PaymentController extends AbstractController
{
    #[
        Route('/calculate-price', methods: [Request::METHOD_POST]),
    ]
    public function calculatePriceAction(
        Request $request,
        RateLimiterFactory $anonymousApiLimiter,
        LoggerInterface $logger,
        PriceCalculator $priceCalculator,
        #[MapRequestPayload(
            acceptFormat: 'json',
            resolver: PriceCalculationDataResolver::class,
        )] PriceCalculationData $data,
    ): JsonResponse {
        try {
            $limiter = $anonymousApiLimiter->create($request->getClientIp());
            if (false === $limiter->consume()->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }

            return $this->json([
                'price' => $priceCalculator->calculatePrice(
                    $data->product->getPrice(),
                    $data->country->getTaxRate(),
                    $data->coupon ? $data->coupon->getValue() : 0,
                    CouponType::PERCENTAGE === $data->coupon?->getType(),
                ),
            ]);
        } catch (\Throwable $throwable) {
            $logger->error('Price calculation failed', [
                'message' => $throwable->getMessage(),
                'ip' => $request->getClientIp(),
                'trace' => $throwable->getTraceAsString(),
                'request' => $request->request->all(),
            ]);

            throw new ApiException('Price calculation failed', Response::HTTP_INTERNAL_SERVER_ERROR, $throwable);
        }
    }
}
