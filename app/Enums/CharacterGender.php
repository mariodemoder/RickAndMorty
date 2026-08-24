<?php

namespace App\Enums;

enum CharacterGender: string
{
    case Female = 'Female';
    case Male = 'Male';
    case Genderless = 'Genderless';
    case Unknown = 'unknown';
}
