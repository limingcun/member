<?php

/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/7/09
 * Time: 下午16:39
 * desc: 积分商城商品过期下架
 */
namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\MallProduct;
use Illuminate\Console\Command;
use Log;
use DB;

class PeriodMall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'period:mall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '积分商城商品过期下架';

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
     * @return mixed
     */
    public function handle()
    {
        $res = MallProduct::where('mall_type', MallProduct::MALLTYPE['invent'])->where('status', '!=', MallProduct::STATUS['takedown'])->get();
        $res = $res->filter(function ($r) {
            if ($r->source->period_type != MallProduct::PERIODTYPE['fixed']) {  //相对时间虚拟商品没有过期下架
                return false;
            } else {
                return Carbon::parse($r->source->period_end)->format('Y-m-d') < Carbon::today()->format('Y-m-d') ? true : false;
            }
        });
        if ($res->isEmpty()) {
            return;
        }
        try {
            DB::beginTransaction();
            foreach($res as $r) {
                $r->status = MallProduct::STATUS['takedown'];
                $r->save();
            }
            DB::commit();
            Log::info('period:mall_success', ['success']);
        } catch (\Exception $exception) {
            DB::rollback();
            Log::info('period:mall_error', [$exception]);
        }
    }
}
