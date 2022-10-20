<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    private $event;
    public function onKernelException(ExceptionEvent $event)
    {

        $this->event = $event;
        $exception = $event->getThrowable();
        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if (!$exception instanceof HttpExceptionInterface) {
            return;
        }
        /** @var CustomHttpException */
        $exception = $event->getThrowable();
        // sends the modified response object to the event
        $event->setResponse(
            new JsonResponse(
                $exception->getPayload(),
                $exception->getStatusCode(),
                $exception->getHeaders()
            )

        );
    }
}
