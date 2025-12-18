<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class PhotoReport extends Model
{
    protected $guarded = [];


    protected $casts = [
        'companycam_meta' => 'array',
        'created_at' => 'date',
        'updated_at' => 'date',
    ];


    public function home(): BelongsTo
    {
        return $this->belongsTo(Home::class);
    }


    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    public function isDocument()
    {
        // If stored as external URL → detect extension
        if (filter_var($this->pdf_path, FILTER_VALIDATE_URL)) {
            return preg_match('/\.(pdf|doc|docx)$/i', $this->pdf_path);
        }

        // If stored locally → check file extension
        $ext = strtolower(pathinfo($this->pdf_path, PATHINFO_EXTENSION));

        return in_array($ext, ['pdf', 'doc', 'docx']);
    }
    public function isImage()
    {
        if (filter_var($this->pdf_path, FILTER_VALIDATE_URL)) {
            return preg_match('/\.(jpg|jpeg|png|webp)$/i', $this->pdf_path);
        }

        $ext = strtolower(pathinfo($this->pdf_path, PATHINFO_EXTENSION));

        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
    }
    public function getUrlAttribute()
    {
        if (filter_var($this->pdf_path, FILTER_VALIDATE_URL)) {
            return $this->pdf_path;
        }

        return asset('storage/' . $this->pdf_path);
    }
    public function getThumbnailUrlAttribute()
    {
        if (!$this->thumbnail_path) return null;

        return asset('storage/' . $this->thumbnail_path);
    }
}
