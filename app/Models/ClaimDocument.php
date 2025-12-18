<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ClaimDocument extends Model
{
    protected $guarded = [];
    protected $fillable = [
        'home_id',
        'title',
        'doc_type',
        'file_path',
        'uploaded_by',
        'date_of_document',
        'notes',
    ];

    protected $casts = ['responses' => 'array', 'date_of_document' => 'date'];


    public function home(): BelongsTo
    {
        return $this->belongsTo(Home::class);
    }
}
