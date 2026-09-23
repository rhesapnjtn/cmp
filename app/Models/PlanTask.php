<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanTask extends Model
{
    protected $fillable = ['plan_id', 'name', 'target_date', 'status', 'progress', 'notes'];

    protected $casts = ['target_date' => 'date'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}