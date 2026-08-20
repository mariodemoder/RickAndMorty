<?php

namespace App\Services\RickAndMorty\Exceptions;

class ConnectionException extends ApiException
{
    public function __construct(string $message = 'Connection to Rick and Morty API failed', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
