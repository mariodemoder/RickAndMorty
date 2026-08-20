<?php

namespace App\Services\RickAndMorty\Exceptions;

class InvalidResponseException extends ApiException
{
    public function __construct(string $message = 'Invalid response from Rick and Morty API', int $statusCode = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
    }
}
