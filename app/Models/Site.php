<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Site extends Model
{
    protected $fillable = ['zone_id', 'code', 'name'];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}