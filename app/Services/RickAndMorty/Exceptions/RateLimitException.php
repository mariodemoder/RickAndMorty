<?php

namespace App\Services\RickAndMorty\Exceptions;

class RateLimitException extends ApiException
{
    public function __construct(
        string $message = 'Rate limited by Rick and Morty API',
        int $statusCode = 429,
        public int $retryAfter = 1,
    ) {
        parent::__construct($message, $statusCode);
    }
}
