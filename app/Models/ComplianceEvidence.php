<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceEvidence extends Model
{
    protected $fillable = ['compliance_item_id', 'file_name', 'file_path', 'uploaded_by'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ComplianceItem::class, 'compliance_item_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}