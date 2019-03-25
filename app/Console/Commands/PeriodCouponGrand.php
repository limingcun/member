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

class PeriodCouponGrand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'period:coupon_grand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发券过期终止';

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
        $grandId = [];
        $res =  CouponGrand::whereIn('status', [CouponGrand::GRANDSTATUS['ungrand'], CouponGrand::GRANDSTATUS['granding'], CouponGrand::GRANDSTATUS['pause']])
                ->whereHas('coupon', function($query) {
                    $query->where('period_type', Coupon::PERIODTYPE['fixed'])->whereDate('period_end', '<', Carbon::today());
                })->select('id')->get();
        if ($res->isEmpty()) {
            return;
        }
        DB::beginTransaction();
        try {
            foreach($res as $r) {
                $grandId[] = $r->id;
            }
            if (count($grandId) > 0) {
                CouponGrand::whereIn('id', $grandId)->update(['status' => CouponGrand::GRANDSTATUS['period']]);
            }
            DB::commit();
            Log::info('PERIOD:COUPON_GRAND_SUCCESS', ['SUCCESS']);
        } catch (\Exception $exception) {
            DB::rollback();
            Log::info('PERIOD:COUPON_GRAND_ERROR', [$exception]);
        }
    }
}
