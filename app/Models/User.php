<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'agency_id',
        'role',
        'crm_location_id',
        'crm_user_id',
        'companycam_user_id',
        'crm_contact_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    /**
     * Get the agency that the user belongs to.
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Get the inspections for a homeowner.
     */
    public function homeownerInspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'homeowner_id');
    }

    /**
     * Get the inspections for an advisor.
     */
    public function advisorInspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'assigned_advisor_id');
    }
     public function homes(): HasMany
    {
        return $this->hasMany(Home::class, 'user_id');
    }
}
