<?php

namespace Tests\Unit\Services;

use App\Services\RickAndMorty\DTOs\EpisodeData;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EpisodeDataTest extends TestCase
{
    public function test_from_api_creates_valid_dto(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Pilot',
            'air_date' => 'December 2, 2013',
            'episode' => 'S01E01',
            'characters' => [
                'https://rickandmortyapi.com/api/character/1',
                'https://rickandmortyapi.com/api/character/2',
            ],
        ];

        $dto = EpisodeData::fromApi($data);

        $this->assertEquals(1, $dto->externalId);
        $this->assertEquals('Pilot', $dto->name);
        $this->assertEquals('December 2, 2013', $dto->airDate);
        $this->assertEquals('S01E01', $dto->episodeCode);
        $this->assertCount(2, $dto->characterUrls);
    }

    public function test_from_api_handles_missing_characters(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Pilot',
            'air_date' => 'December 2, 2013',
            'episode' => 'S01E01',
        ];

        $dto = EpisodeData::fromApi($data);

        $this->assertEquals([], $dto->characterUrls);
    }

    public function test_throws_exception_for_zero_external_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new EpisodeData(
            externalId: 0,
            name: 'Pilot',
            airDate: 'December 2, 2013',
            episodeCode: 'S01E01',
            characterUrls: []
        );
    }

    public function test_throws_exception_for_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new EpisodeData(
            externalId: 1,
            name: '',
            airDate: 'December 2, 2013',
            episodeCode: 'S01E01',
            characterUrls: []
        );
    }

    public function test_throws_exception_for_empty_air_date(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new EpisodeData(
            externalId: 1,
            name: 'Pilot',
            airDate: '',
            episodeCode: 'S01E01',
            characterUrls: []
        );
    }

    public function test_throws_exception_for_empty_episode_code(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new EpisodeData(
            externalId: 1,
            name: 'Pilot',
            airDate: 'December 2, 2013',
            episodeCode: '',
            characterUrls: []
        );
    }
}
