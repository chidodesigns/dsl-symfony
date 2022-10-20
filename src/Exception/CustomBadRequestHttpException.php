<?php
namespace App\Exception;

class CustomBadRequestHttpException extends CustomHttpException
{

    public function __construct(array $payload, int $statusCode , array $headers = [])
    {
        parent::__construct($payload, $statusCode, $headers = []);
    }

}