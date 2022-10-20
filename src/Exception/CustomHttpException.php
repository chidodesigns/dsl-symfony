<?php
namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

abstract class CustomHttpException extends \RuntimeException implements HttpExceptionInterface, Throwable
{
    private $statusCode;
    private $headers;
    private $payload;
    private $status = 0;

    public function __construct(array $payload = [], int $statusCode = 400, array $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->payload = array_merge(['status' => $this->getStatus()], $payload);

        parent::__construct('');
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    public function getStatus():int
    {
        return $this->status;
    }

    public function getStatusCode():int
    {
        return $this->statusCode;
    }

    public function getHeaders():array
    {
        return $this->headers;
    }

    public function getPayload():array
    {
        return $this->payload;
    }
}