<?php

namespace App\Enums;

enum CharacterStatus: string
{
    case Alive = 'Alive';
    case Dead = 'Dead';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Alive => 'Alive',
            self::Dead => 'Dead',
            self::Unknown => 'Unknown',
        };
    }
}
