<?php

namespace App\Console\Commands;

use App\Models\MallOrder;
use App\Models\MallOrderLock;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ClearOrderLock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clr:orderLock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清理订单锁';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $locks = MallOrderLock::where('status', MallOrderLock::STATUS_USEFUL)
            ->with([
                'sku',
                'product',
            ])
            ->where('expire_at', '<', Carbon::now())
            ->get();
        foreach ($locks as $lock){
            $mall_product=$lock->product;
            if ($mall_product->is_specification) {
                $lock->sku()->increment('store', 1);
            } else {
                $mall_product->update([
                    'store' => bcadd($mall_product->store, 1)
                ]);
            }
            $lock->update([
                'status'=>MallOrderLock::STATUS_INVALID
            ]);
        }

    }
}
