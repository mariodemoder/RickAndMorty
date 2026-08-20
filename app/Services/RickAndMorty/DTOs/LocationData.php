<?php

namespace App\Services\RickAndMorty\DTOs;

use InvalidArgumentException;

readonly class LocationData
{
    public function __construct(
        public int $externalId,
        public string $name,
        public string $type,
        public string $dimension,
        public array $residentUrls,
    ) {
        if ($this->externalId <= 0) {
            throw new InvalidArgumentException('external_id must be a positive integer.');
        }

        if ($this->name === '') {
            throw new InvalidArgumentException('name cannot be empty.');
        }

        if ($this->type === '') {
            throw new InvalidArgumentException('type cannot be empty.');
        }

        if ($this->dimension === '') {
            throw new InvalidArgumentException('dimension cannot be empty.');
        }
    }

    public static function fromApi(array $data): self
    {
        return new self(
            externalId: $data['id'],
            name: $data['name'],
            type: $data['type'],
            dimension: $data['dimension'],
            residentUrls: $data['residents'] ?? [],
        );
    }
}
