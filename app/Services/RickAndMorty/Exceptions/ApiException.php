<?php

namespace App\Services\RickAndMorty\Exceptions;

use RuntimeException;

class ApiException extends RuntimeException
{
    public function __construct(string $message = '', int $statusCode = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
    }
}
