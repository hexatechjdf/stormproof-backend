<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;
    protected $fillable = ['agency_id', 'key', 'value'];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}
