<?php

/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/5/24
 * Time: 下午16:39
 * desc: 活动开启和失效定时器
 */
namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Active;
use Illuminate\Console\Command;
use Log;
use DB;

class PeriodActive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'period:active';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '活动开启和失效';

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
     * 设置活动开启status为1,活动完成为3
     * 0为待发放，1为进行中，2为已停止，3为已完成
     * @return mixed
     */
    public function handle()
    {
        $start = Active::where('status', Active::STATUS['tostart'])->where('period_start', '<=', Carbon::today())->get();
        $end = Active::whereIn('status', [Active::STATUS['starting'], Active::STATUS['pause']])->where('period_end', '<', Carbon::today())->get();
        if($start->isEmpty() && $end->isEmpty()) {
            return;
        }
        if(!$start->isEmpty()) {
            try {
                DB::beginTransaction();
                foreach($start as $s) {
                    $s->status = Active::STATUS['starting'];
                    $s->save();
                }
                DB::commit();
                Log::info('period:active_start_success', ['success']);
            } catch (\Exception $exception) {
                DB::rollback();
                Log::info('period:active_start_error', [$exception]);
            }
        }
        if(!$end->isEmpty()) {
            try {
                DB::beginTransaction();
                foreach($end as $d) {
                    $d->status = Active::STATUS['finish'];
                    $d->save();
                }
                DB::commit();
                Log::info('period:active_end_success', ['success']);
            } catch (\Exception $exception) {
                DB::rollback();
                Log::info('period:active_end_error', [$exception]);
            }
        }
    }
}
