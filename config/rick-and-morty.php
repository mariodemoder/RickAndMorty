<?php

return [
    'base_url' => env('RICK_AND_MORTY_API_URL', 'https://rickandmortyapi.com/api'),
    'connect_timeout' => (int) env('RICK_AND_MORTY_CONNECT_TIMEOUT', 10),
    'timeout' => (int) env('RICK_AND_MORTY_TIMEOUT', 30),
    'max_retries' => (int) env('RICK_AND_MORTY_MAX_RETRIES', 3),
    'verify_ssl' => env('RICK_AND_MORTY_VERIFY_SSL', true),
    'request_delay_ms' => (int) env('RICK_AND_MORTY_REQUEST_DELAY_MS', 200),
];
