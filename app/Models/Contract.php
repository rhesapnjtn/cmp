<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contract_number', 'name', 'type', 'unit_id', 'site_id', 'vendor',
        'contract_value', 'used_value', 'currency', 'start_date', 'end_date', 'status',
        'maintenance_type', 'maintenance_progress', 'remaining_work', 'work_progress',
        'pic_user_id', 'year', 'notes',
    ];

    protected $casts = [
        'contract_value' => 'decimal:2',
        'used_value' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function remainingValue(): Attribute
    {
        return Attribute::get(fn () => $this->contract_value - $this->used_value);
    }

    protected static function booted(): void
    {
        static::saving(function (Contract $contract) {
            if ($contract->status === 'completed') {
                $contract->maintenance_progress = 100;
                $contract->work_progress = 100;
            }
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

    public function milestones(): HasMany
    {
        return $this->hasMany(ContractMilestone::class);
    }
}