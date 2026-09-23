<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractMilestone extends Model
{
    protected $fillable = ['contract_id', 'name', 'planned_date', 'actual_date', 'status', 'notes'];

    protected $casts = [
        'planned_date' => 'date',
        'actual_date' => 'date',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}