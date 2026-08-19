<?php

namespace App\Enums;

enum CharacterGender: string
{
    case Female = 'Female';
    case Male = 'Male';
    case Genderless = 'Genderless';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Female => 'Female',
            self::Male => 'Male',
            self::Genderless => 'Genderless',
            self::Unknown => 'Unknown',
        };
    }
}
