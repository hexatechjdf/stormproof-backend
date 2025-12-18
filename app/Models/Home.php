<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Home extends Model
{
protected $guarded = [];


protected $casts = [
'latitude' => 'decimal:7',
'longitude' => 'decimal:7',
'extra' => 'array',
];


public function user(): BelongsTo
{
return $this->belongsTo(User::class);
}


public function projects(): HasMany
{
return $this->hasMany(Project::class);
}


public function photoReports(): HasMany
{
return $this->hasMany(PhotoReport::class);
}


public function claimDocuments(): HasMany
{
return $this->hasMany(ClaimDocument::class);
}
}