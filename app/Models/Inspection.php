<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'homeowner_id',
        'agency_id',
        'assigned_advisor_id',
        'status',
        'trigger_type',
        'property_address',
        'zip_code',
        'admin_notes',
        'advisor_notes',
        'scheduled_at',
        'companycam_project_id'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    /**
     * Get the homeowner that owns the inspection.
     */
    public function homeowner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'homeowner_id');
    }

    /**
     * Get the advisor assigned to the inspection.
     */
    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_advisor_id');
    }

    /**
     * Get the agency associated with the inspection.
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Get the preferred schedule dates for the inspection.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(InspectionSchedule::class);
    }
    /**
     * Get the broadcast offers for the inspection.
     */
    public function broadcasts(): HasMany
    {
        return $this->hasMany(InspectionBroadcast::class);
    }
    public function partnerJob(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PartnerJob::class); // Corrected model
    }
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    public function home(): BelongsTo
    {
        return $this->belongsTo(Home::class);
    }
}
