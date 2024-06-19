<?php

namespace App\EventListener;

use App\Service\Api\Exception\ApiException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

#[AsEventListener]
final class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!($exception instanceof ApiException)) {
            return;
        }

        $event->setResponse(
            new JsonResponse(
                [
                    'error' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                ],
                $exception->getCode(),
            )
        );
    }
}
