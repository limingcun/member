<?php

namespace App\Jobs;

use App\Models\Member;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Controllers\Api\IndexController;
use DB;
use Log;
use Carbon\Carbon;
use IQuery;

class TestQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $order_id;
    protected $list;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order_id, $list)
    {
        $this->order_id = $order_id;
        $this->list = $list;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('bbbb', [$this->order_id.'------'.$this->list]);
    }
}
