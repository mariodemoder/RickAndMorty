<?php

namespace App\Services\RickAndMorty\DTOs;

use InvalidArgumentException;

readonly class EpisodeData
{
    public function __construct(
        public int $externalId,
        public string $name,
        public string $airDate,
        public string $episodeCode,
        public array $characterUrls,
    ) {
        if ($this->externalId <= 0) {
            throw new InvalidArgumentException('external_id must be a positive integer.');
        }

        if ($this->name === '') {
            throw new InvalidArgumentException('name cannot be empty.');
        }

        if ($this->airDate === '') {
            throw new InvalidArgumentException('air_date cannot be empty.');
        }

        if ($this->episodeCode === '') {
            throw new InvalidArgumentException('episode_code cannot be empty.');
        }
    }

    public static function fromApi(array $data): self
    {
        return new self(
            externalId: $data['id'],
            name: $data['name'],
            airDate: $data['air_date'],
            episodeCode: $data['episode'],
            characterUrls: $data['characters'] ?? [],
        );
    }
}
