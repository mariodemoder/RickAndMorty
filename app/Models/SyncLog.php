<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'started_at',
        'finished_at',
        'locations_count',
        'episodes_count',
        'characters_count',
        'error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'locations_count' => 'integer',
        'episodes_count' => 'integer',
        'characters_count' => 'integer',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(SyncLogEntry::class);
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function duration(): ?float
    {
        if (! $this->started_at || ! $this->finished_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->finished_at);
    }
}
