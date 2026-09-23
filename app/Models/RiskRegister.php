<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskRegister extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'risk_code', 'unit_id', 'site_id', 'title', 'description', 'category',
        'likelihood', 'impact', 'risk_score', 'risk_level', 'mitigation', 'contingency',
        'pic_user_id', 'status', 'due_date', 'year', 'quarter', 'evidence',
    ];

    protected $casts = ['due_date' => 'date'];

    protected static function booted(): void
    {
        static::saving(function (RiskRegister $risk) {
            $score = $risk->likelihood * $risk->impact;
            $risk->risk_score = $score;
            $risk->risk_level = match (true) {
                $score >= 15 => 'critical',
                $score >= 9 => 'high',
                $score >= 4 => 'medium',
                default => 'low',
            };
        });
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }
}