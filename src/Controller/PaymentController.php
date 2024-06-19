<?php

namespace App\Controller;

use App\Http\Argument\PriceCalculationData;
use App\Http\Argument\PurchaseData;
use App\Http\ArgumentResolver\PriceCalculationDataResolver;
use App\Http\ArgumentResolver\PurchaseDataResolver;
use App\Service\Api\Exception\ApiException;
use App\Service\Payment\Coupon\Type as CouponType;
use App\Service\Payment\Exception\PaymentException;
use App\Service\Payment\PaymentProcessor;
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

    #[
        Route('/purchase', methods: [Request::METHOD_POST]),
    ]
    public function purchaseAction(
        Request $request,
        PaymentProcessor $paymentProcessor,
        PriceCalculator $priceCalculator,
        #[MapRequestPayload(
            acceptFormat: 'json',
            resolver: PurchaseDataResolver::class,
        )] PurchaseData $data,
        LoggerInterface $logger,
    ): JsonResponse {
        $logger->info('Start payment', [
            'ip' => $request->getClientIp(),
            'request' => $request->request->all(),
        ]);

        try {
            $paymentProcessor->pay(
                $data->paymentMethod,
                $priceCalculator->calculatePrice(
                    $data->product->getPrice(),
                    $data->country->getTaxRate(),
                    $data->coupon ? $data->coupon->getValue() : 0,
                    CouponType::PERCENTAGE === $data->coupon?->getType()
                )
            );

            $responseData = [
                'success' => true,
            ];
        } catch (PaymentException $exception) {
            $logger->error('Payment failed', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'ip' => $request->getClientIp(),
                'request' => $request->request->all(),
            ]);

            throw new ApiException('Payment failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Throwable $throwable) {
            $logger->error('Payment failed', [
                'message' => $throwable->getMessage(),
                'trace' => $throwable->getTraceAsString(),
                'ip' => $request->getClientIp(),
                'request' => $request->request->all(),
            ]);

            throw new ApiException('Payment failed: unknown error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json($responseData);
    }
}
