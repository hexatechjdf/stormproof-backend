<?php

namespace App\Models;

use App\Helper\CRM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CrmAuths extends Model
{
    protected $table = 'crm_tokens';
    use HasFactory;
    protected $guarded = [];
    public function urefresh(): bool
    {
        $is_refresh = false;
        try {
            list($is_refresh, $token) = CRM::getRefreshToken($this->user_id, $this, true);
            // Log::info("Token refreshed successfully. New token:" . $token);
        } catch (\Exception $e) {
            Log::info("Exception Error Refresh token:" . $e->getMessage());
            return 500;
        }
        return $is_refresh;
    }
   public function user()
{
    return $this->belongsTo(User::class);
}
}
