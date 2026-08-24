<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncRawResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'sync_log_id',
        'resource_type',
        'page_number',
        'total_pages',
        'response_body',
        'items_count',
    ];

    public function syncLog(): BelongsTo
    {
        return $this->belongsTo(SyncLog::class);
    }

    public function getDecompressedBody(): ?array
    {
        if ($this->response_body === null) {
            return null;
        }

        $decompressed = @gzuncompress($this->response_body);

        if ($decompressed === false) {
            return null;
        }

        return json_decode($decompressed, true);
    }
}
