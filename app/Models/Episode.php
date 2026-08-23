<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'name',
        'air_date',
        'episode_code',
    ];

    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class);
    }

    public function scopeByName(Builder $query, ?string $name): Builder
    {
        return $name ? $query->where('name', 'like', "%{$name}%") : $query;
    }

    public function scopeByEpisodeCode(Builder $query, ?string $episode): Builder
    {
        return $episode ? $query->where('episode_code', 'like', "%{$episode}%") : $query;
    }
}
