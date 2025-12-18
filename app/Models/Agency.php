<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status'];

    /**
     * Get all of the users for the agency.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all of the settings for the agency.
     */
    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }
    public function crmToken()
    {
        return $this->hasOne(CrmAuths::class);
    }
}
