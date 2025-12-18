<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Jobs\UpdateRefreshToken;
use Carbon\Carbon;

class ProcessRefreshToken implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $page;
    /**
     * Create a new job instance.
     */
    public function __construct($page = 1)
    {
        $this->page = $page;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $limit = 40;
            $currentPage = $this->page - 1;
            $skip = $currentPage * $limit;
            $rl = User::whereHas('token')->skip($skip)->take($limit)->get();
            if ($rl->isNotEmpty()) {
                foreach ($rl as $r) {
                    dispatch((new UpdateRefreshToken($r->id))->delay(Carbon::now()->addSeconds(2)))->onQueue('refresh_token');
                }
                dispatch((new ProcessRefreshToken($this->page + 1))->delay(Carbon::now()->addSeconds(2)))->onQueue('refresh_token');
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
