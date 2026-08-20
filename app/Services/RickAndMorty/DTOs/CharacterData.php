<?php

namespace App\Services\RickAndMorty\DTOs;

use InvalidArgumentException;

readonly class CharacterData
{
    public function __construct(
        public int $externalId,
        public string $name,
        public string $status,
        public string $species,
        public string $type,
        public string $gender,
        public string $image,
        public string $originUrl,
        public string $locationUrl,
        public array $episodeUrls,
    ) {
        if ($this->externalId <= 0) {
            throw new InvalidArgumentException('external_id must be a positive integer.');
        }

        if ($this->name === '') {
            throw new InvalidArgumentException('name cannot be empty.');
        }

        if ($this->status === '') {
            throw new InvalidArgumentException('status cannot be empty.');
        }

        if ($this->species === '') {
            throw new InvalidArgumentException('species cannot be empty.');
        }

        if ($this->gender === '') {
            throw new InvalidArgumentException('gender cannot be empty.');
        }

        if ($this->image === '') {
            throw new InvalidArgumentException('image cannot be empty.');
        }
    }

    public static function fromApi(array $data): self
    {
        return new self(
            externalId: $data['id'],
            name: $data['name'],
            status: $data['status'],
            species: $data['species'],
            type: $data['type'] ?? '',
            gender: $data['gender'],
            image: $data['image'],
            originUrl: $data['origin']['url'] ?? '',
            locationUrl: $data['location']['url'] ?? '',
            episodeUrls: $data['episode'] ?? [],
        );
    }
}
