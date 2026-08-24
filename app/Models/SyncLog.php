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
        'batch_id',
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
}
