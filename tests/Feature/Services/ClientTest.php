<?php

namespace Tests\Feature\Services;

use App\Services\RickAndMorty\Client;
use App\Services\RickAndMorty\DTOs\CharacterData;
use App\Services\RickAndMorty\DTOs\EpisodeData;
use App\Services\RickAndMorty\DTOs\LocationData;
use App\Services\RickAndMorty\Exceptions\ConnectionException;
use App\Services\RickAndMorty\Exceptions\InvalidResponseException;
use Illuminate\Http\Client\ConnectionException as LaravelConnectionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = app(Client::class);
    }

    public function test_get_characters_returns_paginated_response(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/character*' => Http::response([
                'info' => ['count' => 826, 'pages' => 42, 'next' => 'https://rickandmortyapi.com/api/character?page=2', 'prev' => null],
                'results' => [
                    [
                        'id' => 1,
                        'name' => 'Rick Sanchez',
                        'status' => 'Alive',
                        'species' => 'Human',
                        'type' => '',
                        'gender' => 'Male',
                        'image' => 'https://rickandmortyapi.com/api/character/avatar/1.jpeg',
                        'origin' => ['url' => 'https://rickandmortyapi.com/api/location/1'],
                        'location' => ['url' => 'https://rickandmortyapi.com/api/location/20'],
                        'episode' => ['https://rickandmortyapi.com/api/episode/1'],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->client->getCharacters(1);

        $this->assertArrayHasKey('raw', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('info', $result['data']);
        $this->assertArrayHasKey('results', $result['data']);
        $this->assertEquals(826, $result['data']['info']['count']);
        $this->assertCount(1, $result['data']['results']);
        $this->assertEquals('Rick Sanchez', $result['data']['results'][0]['name']);
    }

    public function test_get_character_returns_dto(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/character/1' => Http::response([
                'id' => 1,
                'name' => 'Rick Sanchez',
                'status' => 'Alive',
                'species' => 'Human',
                'type' => '',
                'gender' => 'Male',
                'image' => 'https://rickandmortyapi.com/api/character/avatar/1.jpeg',
                'origin' => ['url' => 'https://rickandmortyapi.com/api/location/1'],
                'location' => ['url' => 'https://rickandmortyapi.com/api/location/20'],
                'episode' => [
                    'https://rickandmortyapi.com/api/episode/1',
                    'https://rickandmortyapi.com/api/episode/2',
                ],
            ], 200),
        ]);

        $dto = $this->client->getCharacter(1);

        $this->assertInstanceOf(CharacterData::class, $dto);
        $this->assertEquals(1, $dto->externalId);
        $this->assertEquals('Rick Sanchez', $dto->name);
        $this->assertEquals('Alive', $dto->status);
        $this->assertEquals('Human', $dto->species);
        $this->assertEquals('Male', $dto->gender);
        $this->assertCount(2, $dto->episodeUrls);
    }

    public function test_get_locations_returns_paginated_response(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/location*' => Http::response([
                'info' => ['count' => 126, 'pages' => 7, 'next' => 'https://rickandmortyapi.com/api/location?page=2', 'prev' => null],
                'results' => [
                    [
                        'id' => 1,
                        'name' => 'Earth (C-137)',
                        'type' => 'Planet',
                        'dimension' => 'Dimension C-137',
                        'residents' => ['https://rickandmortyapi.com/api/character/1'],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->client->getLocations(1);

        $this->assertArrayHasKey('raw', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('info', $result['data']);
        $this->assertArrayHasKey('results', $result['data']);
        $this->assertEquals(126, $result['data']['info']['count']);
        $this->assertCount(1, $result['data']['results']);
    }

    public function test_get_episodes_returns_paginated_response(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/episode*' => Http::response([
                'info' => ['count' => 51, 'pages' => 3, 'next' => 'https://rickandmortyapi.com/api/episode?page=2', 'prev' => null],
                'results' => [
                    [
                        'id' => 1,
                        'name' => 'Pilot',
                        'air_date' => 'December 2, 2013',
                        'episode' => 'S01E01',
                        'characters' => ['https://rickandmortyapi.com/api/character/1'],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->client->getEpisodes(1);

        $this->assertArrayHasKey('raw', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('info', $result['data']);
        $this->assertArrayHasKey('results', $result['data']);
        $this->assertEquals(51, $result['data']['info']['count']);
        $this->assertCount(1, $result['data']['results']);
    }

    public function test_fetch_all_characters_paginates_through_all_pages(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/character?page=1' => Http::response([
                'info' => ['count' => 3, 'pages' => 2, 'next' => 'https://rickandmortyapi.com/api/character?page=2', 'prev' => null],
                'results' => [
                    ['id' => 1, 'name' => 'Rick', 'status' => 'Alive', 'species' => 'Human', 'type' => '', 'gender' => 'Male', 'image' => 'img.jpg', 'origin' => ['url' => ''], 'location' => ['url' => ''], 'episode' => []],
                    ['id' => 2, 'name' => 'Morty', 'status' => 'Alive', 'species' => 'Human', 'type' => '', 'gender' => 'Male', 'image' => 'img.jpg', 'origin' => ['url' => ''], 'location' => ['url' => ''], 'episode' => []],
                ],
            ], 200),
            'rickandmortyapi.com/api/character?page=2' => Http::response([
                'info' => ['count' => 3, 'pages' => 2, 'next' => null, 'prev' => 'https://rickandmortyapi.com/api/character?page=1'],
                'results' => [
                    ['id' => 3, 'name' => 'Summer', 'status' => 'Alive', 'species' => 'Human', 'type' => '', 'gender' => 'Female', 'image' => 'img.jpg', 'origin' => ['url' => ''], 'location' => ['url' => ''], 'episode' => []],
                ],
            ], 200),
        ]);

        $collection = $this->client->fetchAllCharacters();

        $this->assertCount(3, $collection);
        $this->assertInstanceOf(CharacterData::class, $collection->first());
        $this->assertEquals('Rick', $collection->first()->name);
        $this->assertEquals('Summer', $collection->last()->name);
    }

    public function test_throws_invalid_response_on_non_200_status(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/character*' => Http::response([], 500),
        ]);

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('Unexpected HTTP status: 500');

        $this->client->getCharacters(1);
    }

    public function test_throws_invalid_response_on_invalid_json(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/character*' => Http::response('not valid json', 200, ['Content-Type' => 'application/json']),
        ]);

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('Response body is not valid JSON.');

        $this->client->getCharacters(1);
    }

    public function test_throws_invalid_response_on_missing_keys(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/character*' => Http::response([
                'unexpected' => 'data',
            ], 200),
        ]);

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('Response is missing expected "info" or "results" keys.');

        $this->client->getCharacters(1);
    }

    public function test_retries_on_connection_exception(): void
    {
        $callCount = 0;

        Http::fake(function ($request) use (&$callCount) {
            $callCount++;

            if ($callCount < 3) {
                throw new LaravelConnectionException('Connection refused');
            }

            return Http::response([
                'info' => ['count' => 1, 'pages' => 1, 'next' => null, 'prev' => null],
                'results' => [
                    ['id' => 1, 'name' => 'Rick', 'status' => 'Alive', 'species' => 'Human', 'type' => '', 'gender' => 'Male', 'image' => 'img.jpg', 'origin' => ['url' => ''], 'location' => ['url' => ''], 'episode' => []],
                ],
            ], 200);
        });

        $result = $this->client->getCharacters(1);

        $this->assertEquals(3, $callCount);
        $this->assertCount(1, $result['data']['results']);
    }

    public function test_throws_connection_exception_after_max_retries(): void
    {
        $callCount = 0;

        Http::fake(function ($request) use (&$callCount) {
            $callCount++;
            throw new LaravelConnectionException('Connection refused');
        });

        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('Failed to connect to Rick and Morty API after 3 attempts.');

        $this->client->getCharacters(1);

        $this->assertEquals(3, $callCount);
    }

    public function test_get_location_returns_dto(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/location/1' => Http::response([
                'id' => 1,
                'name' => 'Earth (C-137)',
                'type' => 'Planet',
                'dimension' => 'Dimension C-137',
                'residents' => ['https://rickandmortyapi.com/api/character/1'],
            ], 200),
        ]);

        $dto = $this->client->getLocation(1);

        $this->assertInstanceOf(LocationData::class, $dto);
        $this->assertEquals(1, $dto->externalId);
        $this->assertEquals('Earth (C-137)', $dto->name);
        $this->assertEquals('Planet', $dto->type);
        $this->assertEquals('Dimension C-137', $dto->dimension);
    }

    public function test_get_episode_returns_dto(): void
    {
        Http::fake([
            'rickandmortyapi.com/api/episode/1' => Http::response([
                'id' => 1,
                'name' => 'Pilot',
                'air_date' => 'December 2, 2013',
                'episode' => 'S01E01',
                'characters' => ['https://rickandmortyapi.com/api/character/1'],
            ], 200),
        ]);

        $dto = $this->client->getEpisode(1);

        $this->assertInstanceOf(EpisodeData::class, $dto);
        $this->assertEquals(1, $dto->externalId);
        $this->assertEquals('Pilot', $dto->name);
        $this->assertEquals('December 2, 2013', $dto->airDate);
        $this->assertEquals('S01E01', $dto->episodeCode);
    }
}
