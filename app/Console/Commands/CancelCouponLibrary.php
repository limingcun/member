<?php

/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/11/29
 * Time: 上午10:13
 * desc: 设置积分经验值补录区间
 */
namespace App\Console\Commands;

use App\Models\CouponLibrary;
use App\Policies\CouponLibrary\QueueCouponPolicy;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;
use DB;
use IQuery;

class CancelCouponLibrary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cancel:coupon_library';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '核销优惠券';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        DB::beginTransaction();
        try {
            $start = Carbon::today()->startOfDay();
            $end = Carbon::today()->endOfDay();
            $sql = "select o.coupon_library_id, o.paid_at, o.id, o.discount_fee from orders o left join member_scores ms on o.id = ms.source_id where o.paid_at is not null and o.coupon_library_id != '' and
o.refund_status = 'NO_REFUND' and o.user_id > 0 and ms.id is null and o.created_at between '".$start."' and '" .$end."'";
            $res = DB::select($sql);
            if (count($res) == 0) {
                return false;
            }
            foreach($res as $r) {
                $lib_ids = explode(',', $r->coupon_library_id);
                foreach($lib_ids as $lib_id) {
                    $library = CouponLibrary::find($lib_id);
                    $library->order_id = $r->id;
                    if ($library->policy != QueueCouponPolicy::class) {
                        $library->discount_fee = $r->discount_fee;
                    }
                    $library->status = 2;
                    $library->used_at = $r->paid_at;
                    $library->save();
                }
            }
            DB::commit();
            Log::info('cancel:library_success', ['success']);
        } catch (\Exception $exception) {
            DB::rollback();
            Log::info('cancel:library_error', [$exception]);
        } 
    }
}
