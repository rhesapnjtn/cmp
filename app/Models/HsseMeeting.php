<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HsseMeeting extends Model
{
    protected $fillable = [
        'level', 'title', 'unit_id', 'zone_id', 'site_id', 'quarter', 'year',
        'target_count', 'planned_date', 'realized_date', 'status', 'notes',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'realized_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (HsseMeeting $meeting) {
            if ($meeting->status === 'done') {
                $meeting->realized_date ??= now()->toDateString();
            }
        });
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}