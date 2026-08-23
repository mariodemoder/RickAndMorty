<?php

namespace Tests\Unit\Services;

use App\Services\RickAndMorty\DTOs\LocationData;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LocationDataTest extends TestCase
{
    public function test_from_api_creates_valid_dto(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Earth',
            'type' => 'Planet',
            'dimension' => 'Dimension C-137',
            'residents' => [
                'https://rickandmortyapi.com/api/character/1',
                'https://rickandmortyapi.com/api/character/2',
            ],
        ];

        $dto = LocationData::fromApi($data);

        $this->assertEquals(1, $dto->externalId);
        $this->assertEquals('Earth', $dto->name);
        $this->assertEquals('Planet', $dto->type);
        $this->assertEquals('Dimension C-137', $dto->dimension);
        $this->assertCount(2, $dto->residentUrls);
    }

    public function test_from_api_handles_missing_residents(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Earth',
            'type' => 'Planet',
            'dimension' => 'Dimension C-137',
        ];

        $dto = LocationData::fromApi($data);

        $this->assertEquals([], $dto->residentUrls);
    }

    public function test_throws_exception_for_zero_external_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LocationData(
            externalId: 0,
            name: 'Earth',
            type: 'Planet',
            dimension: 'Dimension C-137',
            residentUrls: []
        );
    }

    public function test_throws_exception_for_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LocationData(
            externalId: 1,
            name: '',
            type: 'Planet',
            dimension: 'Dimension C-137',
            residentUrls: []
        );
    }

    public function test_throws_exception_for_empty_type(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LocationData(
            externalId: 1,
            name: 'Earth',
            type: '',
            dimension: 'Dimension C-137',
            residentUrls: []
        );
    }

    public function test_throws_exception_for_empty_dimension(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LocationData(
            externalId: 1,
            name: 'Earth',
            type: 'Planet',
            dimension: '',
            residentUrls: []
        );
    }
}
