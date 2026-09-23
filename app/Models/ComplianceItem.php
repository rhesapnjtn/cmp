<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplianceItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'requirement', 'regulation', 'category', 'unit_id', 'pic_user_id', 'deadline',
        'status', 'progress', 'year', 'quarter', 'notes',
    ];

    protected $casts = ['deadline' => 'date'];

    protected static function booted(): void
    {
        static::saving(function (ComplianceItem $item) {
            if ($item->status === 'compliant') {
                $item->progress = 100;
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

    public function evidence(): HasMany
    {
        return $this->hasMany(ComplianceEvidence::class);
    }
}