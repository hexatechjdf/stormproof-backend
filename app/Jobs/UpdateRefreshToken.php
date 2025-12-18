<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CrmAuths;
use App\Models\CrmToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateRefreshToken implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $rf = CrmAuths::where('user_id', $this->userId)->first();
            if ($rf) {
                $status = $rf->urefresh();
                if ($status === 500) {
                    dispatch((new UpdateRefreshToken($this->userId))->delay(Carbon::now()->addMinutes(5)))->onQueue('refresh_token');
                }
            }
        } catch (\Throwable $th) {
            Log::error('Caught exception: ' . $th->getMessage(), [
                'exception' => $th,
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ]);
        }
    }
}
