<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionBroadcast extends Model
{
    use HasFactory;
    protected $fillable = ['inspection_id', 'advisor_id', 'status'];
}
