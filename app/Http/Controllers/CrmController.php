<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessRefreshToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CrmController extends Controller
{
    public function refreshCrmTokens()
    {
        dispatch(new ProcessRefreshToken(1))->onQueue('refresh_token');
        Log::info('Token refresh job dispatched (page 1) via URL.');
        return response()->json([
            'status' => 'success',
            'message' => 'CRM token refresh job dispatched successfully (page 1).'
        ]);
    }
}
