<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'unit_id', 'title', 'description', 'category', 'target_date', 'realized_date',
        'status', 'progress', 'year', 'quarter', 'pic_user_id', 'evidence', 'notes',
    ];

    protected $casts = [
        'target_date' => 'date',
        'realized_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (Plan $plan) {
            if ($plan->status === 'done') {
                $plan->progress = 100;
                $plan->realized_date ??= now()->toDateString();
            }
        });
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(PlanTask::class);
    }
}