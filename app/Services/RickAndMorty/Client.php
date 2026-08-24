<?php

namespace App\Services\RickAndMorty;

use App\Services\RickAndMorty\DTOs\CharacterData;
use App\Services\RickAndMorty\DTOs\EpisodeData;
use App\Services\RickAndMorty\DTOs\LocationData;
use App\Services\RickAndMorty\Exceptions\ConnectionException;
use App\Services\RickAndMorty\Exceptions\InvalidResponseException;
use Illuminate\Http\Client\ConnectionException as LaravelConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Client
{
    protected string $baseUrl;

    protected int $connectTimeout;

    protected int $timeout;

    protected int $maxRetries;

    protected bool $verifySsl;

    public function __construct()
    {
        $this->baseUrl = config('rick-and-morty.base_url', 'https://rickandmortyapi.com/api');
        $this->connectTimeout = (int) config('rick-and-morty.connect_timeout', 10);
        $this->timeout = (int) config('rick-and-morty.timeout', 30);
        $this->maxRetries = (int) config('rick-and-morty.max_retries', 3);
        $this->verifySsl = (bool) config('rick-and-morty.verify_ssl', true);
    }

    public function getCharacters(int $page = 1): array
    {
        $response = $this->request('character', ['page' => $page]);

        return [
            'raw' => $response['raw'],
            'data' => $this->validatePaginatedResponse($response['data']),
        ];
    }

    public function getLocations(int $page = 1): array
    {
        $response = $this->request('location', ['page' => $page]);

        return [
            'raw' => $response['raw'],
            'data' => $this->validatePaginatedResponse($response['data']),
        ];
    }

    public function getEpisodes(int $page = 1): array
    {
        $response = $this->request('episode', ['page' => $page]);

        return [
            'raw' => $response['raw'],
            'data' => $this->validatePaginatedResponse($response['data']),
        ];
    }

    public function fetchAllCharacters(): Collection
    {
        return $this->fetchAll('character', CharacterData::class);
    }

    public function fetchAllLocations(): Collection
    {
        return $this->fetchAll('location', LocationData::class);
    }

    public function fetchAllEpisodes(): Collection
    {
        return $this->fetchAll('episode', EpisodeData::class);
    }

    public function getCharacter(int $id): CharacterData
    {
        $response = $this->request("character/{$id}", [], false);

        return CharacterData::fromApi($response['data']);
    }

    public function getEpisode(int $id): EpisodeData
    {
        $response = $this->request("episode/{$id}", [], false);

        return EpisodeData::fromApi($response['data']);
    }

    public function getLocation(int $id): LocationData
    {
        $response = $this->request("location/{$id}", [], false);

        return LocationData::fromApi($response['data']);
    }

    protected function request(string $endpoint, array $params = [], bool $expectPaginated = true): array
    {
        return $this->retry(function () use ($endpoint, $params, $expectPaginated) {
            $url = rtrim($this->baseUrl, '/').'/'.$endpoint;

            $http = Http::connectTimeout($this->connectTimeout)
                ->timeout($this->timeout);

            if (! $this->verifySsl) {
                $http = $http->withoutVerifying();
            }

            $response = $http->get($url, $params);

            $this->validateResponse($response, $expectPaginated);

            return [
                'raw' => $response->body(),
                'data' => $response->json(),
            ];
        });
    }

    protected function validateResponse(Response $response, bool $expectPaginated = true): void
    {
        if ($response->status() !== 200) {
            throw new InvalidResponseException(
                "Unexpected HTTP status: {$response->status()}",
                $response->status()
            );
        }

        $decoded = json_decode($response->body(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidResponseException('Response body is not valid JSON.');
        }

        if ($expectPaginated && (! is_array($decoded) || ! isset($decoded['info']) || ! isset($decoded['results']))) {
            throw new InvalidResponseException('Response is missing expected "info" or "results" keys.');
        }
    }

    protected function validatePaginatedResponse(array $data): array
    {
        return [
            'info' => $data['info'],
            'results' => $data['results'],
        ];
    }

    protected function fetchAll(string $resource, string $dtoClass): Collection
    {
        $results = collect();
        $page = 1;
        $hasMorePages = true;

        while ($hasMorePages) {
            $response = $this->request($resource, ['page' => $page]);
            $validated = $this->validatePaginatedResponse($response['data']);

            foreach ($validated['results'] as $item) {
                $results->push($dtoClass::fromApi($item));
            }

            $hasMorePages = $page < $validated['info']['pages'];
            $page++;
        }

        return $results;
    }

    protected function retry(callable $callback): mixed
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                return $callback();
            } catch (LaravelConnectionException $e) {
                $lastException = $e;

                if ($attempt < $this->maxRetries) {
                    $sleepMs = (int) (1000 * pow(2, $attempt - 1));

                    Log::warning('Rick and Morty API connection failed, retrying...', [
                        'attempt' => $attempt,
                        'sleep_ms' => $sleepMs,
                        'error' => $e->getMessage(),
                    ]);

                    usleep($sleepMs * 1000);
                }
            }
        }

        Log::error('Rick and Morty API connection failed after all retries', [
            'attempts' => $this->maxRetries,
            'error' => $lastException?->getMessage(),
        ]);

        throw new ConnectionException(
            "Failed to connect to Rick and Morty API after {$this->maxRetries} attempts.",
            $lastException
        );
    }
}
