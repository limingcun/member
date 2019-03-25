<?php

/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/11/05
 * Time: 上午11:04
 * desc: 优惠券模板过期
 */
namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\CouponGrand;
use Illuminate\Console\Command;
use Log;
use DB;

class PeriodCoupon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'period:coupon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '优惠券模板过期';

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
        $couponId = [];
        $res = Coupon::whereIn('status', Coupon::STATUS['start'], Coupon::STATUS['end'])->where('period_type', Coupon::PERIODTYPE['fixed'])
             ->whereDate('period_end', '<', Carbon::today())->select('id')->get();
        if ($res->isEmpty()) {
            return;
        }
        DB::beginTransaction();
        try {
            foreach($res as $r) {
                $couponId[] = $r->id;
            }
            if (count($couponId) > 0) {
                Coupon::whereIn('id', $couponId)->update(['status' => Coupon::STATUS['period']]);
            }
            DB::commit();
            Log::info('PERIOD:COUPON_SUCCESS', ['SUCCESS']);
        } catch (\Exception $exception) {
            DB::rollback();
            Log::info('PERIOD:COUPON_ERROR', [$exception]);
        }
    }
}
