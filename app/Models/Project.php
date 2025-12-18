<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Project extends Model
{
protected $guarded = [];


protected $casts = ['metadata' => 'array',
'start_date' => 'date',
'end_date' => 'date',
'created_at' => 'date',
'updated_at' => 'date',];



public function home(): BelongsTo
{
return $this->belongsTo(Home::class);
}


public function inspections(): HasMany
{
return $this->hasMany(Inspection::class);
}


public function photoReports(): HasMany
{
return $this->hasMany(PhotoReport::class);
}
}