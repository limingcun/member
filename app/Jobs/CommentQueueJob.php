<?php

namespace App\Jobs;

use App\Models\Member;
use App\Models\Order;
use App\Models\MemberScore;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Controllers\Api\IndexController;
use DB;
use Log;

class CommentQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $order_id;
    protected $coupon_flag;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order_id, $coupon_flag)
    {
        $this->order_id = $order_id;
        $this->coupon_flag = $coupon_flag;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $con = new IndexController();
        $order_id = $this->order_id;
        $order = Order::find($order_id);
        $user_id = $order->user_id;
        $member = Member::where('user_id', $user_id)->first();
        DB::beginTransaction();
        try {
            $dataArr = $con->memberData($member, $order);  //星球会员获取积分和经验值
            //保存memberScore
            $con->saveMemberScore($member, $order, $dataArr['point']);  //保存消费获取积分
            $dataArr = $con->extraPoint($member, $order, $dataArr); //会员纪念日获得额外积分
            //优惠券核销
//            $coupon_flag = 0;
//            if ($order->coupon_library_id) {
//                $coupon_flag = $con->couponLibraryUsed($order);
//            }
            $cup = $con->memberCup($member, $order, $this->coupon_flag);  //钻石会员、黑金会员、黑钻会员累计杯数
            //保存member表数据信息
            $member = $con->saveMember($member, $order, $user_id, $dataArr, $cup);
            $con->saveMemberExp($member, $order, $dataArr);  //保存经验值
            //app触发极光推送
            if ($order->trade_type == 'APP' || $order->trade_type == 'IPAY') {
                $con->appPush($order, $user_id);
            }
            DB::commit();
            Log::info('inc_score_success', ['inc_success']);
        } catch (\Exception $exception) {
            //捕获并处理异常并回滚
            DB::rollback();
            Log::info('inc_score_error', [$exception]);
        }
    }
}
