<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class HomeQuestionnaire extends Model
{
    protected $guarded = [];


    protected $casts = ['responses' => 'array'];


    public function home(): BelongsTo
    {
        return $this->belongsTo(Home::class);
    }
}
