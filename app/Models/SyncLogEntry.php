<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLogEntry extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'sync_log_id',
        'level',
        'message',
        'context',
        'created_at',
    ];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    public function syncLog(): BelongsTo
    {
        return $this->belongsTo(SyncLog::class);
    }
}
