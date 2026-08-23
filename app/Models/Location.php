<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'name',
        'type',
        'dimension',
    ];

    public function residents(): HasMany
    {
        return $this->hasMany(Character::class, 'current_location_id');
    }

    public function charactersAsOrigin(): HasMany
    {
        return $this->hasMany(Character::class, 'origin_location_id');
    }

    public function scopeByName(Builder $query, ?string $name): Builder
    {
        return $name ? $query->where('name', 'like', "%{$name}%") : $query;
    }

    public function scopeByType(Builder $query, ?string $type): Builder
    {
        return $type ? $query->where('type', 'like', "%{$type}%") : $query;
    }

    public function scopeByDimension(Builder $query, ?string $dimension): Builder
    {
        return $dimension ? $query->where('dimension', 'like', "%{$dimension}%") : $query;
    }
}
