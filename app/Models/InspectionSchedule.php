<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionSchedule extends Model
{
    use HasFactory;
    protected $fillable = ['inspection_id', 'preferred_date'];
    protected $casts = ['preferred_date' => 'datetime'];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }
}
