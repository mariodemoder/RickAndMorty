<?php

namespace Tests\Unit\Services;

use App\Services\RickAndMorty\DTOs\CharacterData;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CharacterDataTest extends TestCase
{
    public function test_from_api_creates_valid_dto(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Rick Sanchez',
            'status' => 'Alive',
            'species' => 'Human',
            'type' => '',
            'gender' => 'Male',
            'image' => 'https://example.com/image.jpeg',
            'origin' => ['url' => 'https://rickandmortyapi.com/api/location/1'],
            'location' => ['url' => 'https://rickandmortyapi.com/api/location/20'],
            'episode' => ['https://rickandmortyapi.com/api/episode/1'],
        ];

        $dto = CharacterData::fromApi($data);

        $this->assertEquals(1, $dto->externalId);
        $this->assertEquals('Rick Sanchez', $dto->name);
        $this->assertEquals('Alive', $dto->status);
        $this->assertEquals('Human', $dto->species);
        $this->assertEquals('', $dto->type);
        $this->assertEquals('Male', $dto->gender);
        $this->assertEquals('https://example.com/image.jpeg', $dto->image);
        $this->assertEquals('https://rickandmortyapi.com/api/location/1', $dto->originUrl);
        $this->assertEquals('https://rickandmortyapi.com/api/location/20', $dto->locationUrl);
        $this->assertEquals(['https://rickandmortyapi.com/api/episode/1'], $dto->episodeUrls);
    }

    public function test_from_api_handles_missing_optional_fields(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Rick Sanchez',
            'status' => 'Alive',
            'species' => 'Human',
            'gender' => 'Male',
            'image' => 'https://example.com/image.jpeg',
        ];

        $dto = CharacterData::fromApi($data);

        $this->assertEquals('', $dto->type);
        $this->assertEquals('', $dto->originUrl);
        $this->assertEquals('', $dto->locationUrl);
        $this->assertEquals([], $dto->episodeUrls);
    }

    public function test_throws_exception_for_zero_external_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('external_id must be a positive integer.');

        new CharacterData(
            externalId: 0,
            name: 'Test',
            status: 'Alive',
            species: 'Human',
            type: '',
            gender: 'Male',
            image: 'https://example.com/image.jpeg',
            originUrl: '',
            locationUrl: '',
            episodeUrls: []
        );
    }

    public function test_throws_exception_for_negative_external_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CharacterData(
            externalId: -1,
            name: 'Test',
            status: 'Alive',
            species: 'Human',
            type: '',
            gender: 'Male',
            image: 'https://example.com/image.jpeg',
            originUrl: '',
            locationUrl: '',
            episodeUrls: []
        );
    }

    public function test_throws_exception_for_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('name cannot be empty.');

        new CharacterData(
            externalId: 1,
            name: '',
            status: 'Alive',
            species: 'Human',
            type: '',
            gender: 'Male',
            image: 'https://example.com/image.jpeg',
            originUrl: '',
            locationUrl: '',
            episodeUrls: []
        );
    }

    public function test_throws_exception_for_empty_status(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('status cannot be empty.');

        new CharacterData(
            externalId: 1,
            name: 'Test',
            status: '',
            species: 'Human',
            type: '',
            gender: 'Male',
            image: 'https://example.com/image.jpeg',
            originUrl: '',
            locationUrl: '',
            episodeUrls: []
        );
    }

    public function test_throws_exception_for_empty_species(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('species cannot be empty.');

        new CharacterData(
            externalId: 1,
            name: 'Test',
            status: 'Alive',
            species: '',
            type: '',
            gender: 'Male',
            image: 'https://example.com/image.jpeg',
            originUrl: '',
            locationUrl: '',
            episodeUrls: []
        );
    }

    public function test_throws_exception_for_empty_gender(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('gender cannot be empty.');

        new CharacterData(
            externalId: 1,
            name: 'Test',
            status: 'Alive',
            species: 'Human',
            type: '',
            gender: '',
            image: 'https://example.com/image.jpeg',
            originUrl: '',
            locationUrl: '',
            episodeUrls: []
        );
    }

    public function test_throws_exception_for_empty_image(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('image cannot be empty.');

        new CharacterData(
            externalId: 1,
            name: 'Test',
            status: 'Alive',
            species: 'Human',
            type: '',
            gender: 'Male',
            image: '',
            originUrl: '',
            locationUrl: '',
            episodeUrls: []
        );
    }
}
