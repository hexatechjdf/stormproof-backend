<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class InspectionAppointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inspection_appointments';

    protected $fillable = [
        'inspection_id',
        'homeowner_id',
        'inspector_id',
        'preferred_datetime',
        'confirmed_datetime',
        'completed_datetime',
        'inspection_type',
        'contact_method',
        'contact_number',
        'contact_email',
        'notes',
        'access_instructions',
        'inspector_notes',
        'status',
        'crm_appointment_id',
        'crm_location_id',
        'crm_calendar_id',
        'crm_contact_id',
    ];

    protected $casts = [
        'preferred_datetime' => 'datetime',
        'confirmed_datetime' => 'datetime',
        'completed_datetime' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function homeowner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'homeowner_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('preferred_datetime', '>', now())
                     ->where('status', '!=', 'cancelled');
    }

    public function scopePast($query)
    {
        return $query->where('preferred_datetime', '<', now());
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('preferred_datetime', $date);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('preferred_datetime', [$startDate, $endDate]);
    }

    /**
     * Accessors & Mutators
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'confirmed' => '<span class="badge bg-success">Confirmed</span>',
            'completed' => '<span class="badge bg-info">Completed</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
            'no_show' => '<span class="badge bg-secondary">No Show</span>',
            default => '<span class="badge bg-light">Unknown</span>',
        };
    }

    public function getInspectionTypeLabel(): string
    {
        return match($this->inspection_type) {
            'general' => 'General Home Inspection',
            'pest' => 'Pest Inspection',
            'mold' => 'Mold Inspection',
            'radon' => 'Radon Testing',
            'termite' => 'Termite Inspection',
            'roof' => 'Roof Inspection',
            'foundation' => 'Foundation Inspection',
            'electrical' => 'Electrical Inspection',
            'plumbing' => 'Plumbing Inspection',
            default => ucfirst($this->inspection_type),
        };
    }

    public function getContactMethodLabel(): string
    {
        return match($this->contact_method) {
            'phone' => 'Phone Call',
            'email' => 'Email',
            'sms' => 'SMS Text',
            'whatsapp' => 'WhatsApp',
            default => ucfirst($this->contact_method),
        };
    }

    /**
     * Check if appointment is in the past
     */
    public function isPast(): bool
    {
        return $this->preferred_datetime < now();
    }

    /**
     * Check if appointment is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->preferred_datetime > now() && $this->status !== 'cancelled';
    }

    /**
     * Check if appointment can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return $this->status !== 'completed' && $this->status !== 'cancelled';
    }

    /**
     * Check if appointment can be confirmed
     */
    public function canBeConfirmed(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get formatted appointment time
     */
    public function getFormattedTime($timezone = null): string
    {
        $dateTime = $this->preferred_datetime;

        if ($timezone) {
            $dateTime = $dateTime->setTimezone($timezone);
        }

        return $dateTime->format('M d, Y \a\t g:i A');
    }

    /**
     * Get time until appointment
     */
    public function getTimeUntilAppointment(): string
    {
        return $this->preferred_datetime->diffForHumans();
    }

    /**
     * Convert to calendar event format
     */
    public function toCalendarEvent(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getInspectionTypeLabel(),
            'start' => $this->preferred_datetime->toIso8601String(),
            'end' => $this->preferred_datetime->addHour()->toIso8601String(),
            'backgroundColor' => $this->getStatusColor(),
            'borderColor' => $this->getStatusColor(),
            'extendedProps' => [
                'status' => $this->status,
                'type' => $this->inspection_type,
                'contact_method' => $this->contact_method,
                'notes' => $this->notes,
            ],
        ];
    }

    /**
     * Get status color for calendar display
     */
    private function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => '#ffc107',      // Yellow
            'confirmed' => '#28a745',    // Green
            'completed' => '#17a2b8',    // Blue
            'cancelled' => '#dc3545',    // Red
            'no_show' => '#6c757d',      // Gray
            default => '#007bff',        // Primary Blue
        };
    }
}