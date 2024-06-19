<?php

namespace App\EventListener;

use App\Service\Api\Exception\ApiException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[AsEventListener]
final class HttpExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpException) {
            throw new ApiException($exception->getMessage(), $exception->getStatusCode(), $exception);
        }
    }
}
