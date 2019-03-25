<?php

namespace App\Console\Commands;

use App\Http\Controllers\WechatController;
use App\Models\Coupon;
use App\Models\CouponLibrary;
use App\Models\GiftRecord;
use App\Models\Member;
use App\Models\MemberCardRecord;
use App\Models\MemberScore;
use Carbon\Carbon;
use Illuminate\Console\Command;
use DB;

class RefundCardOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refund:card_order {user_ids?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '星球会员购卡退款';

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
        $id_str = $this->argument('user_ids');
        $ids = explode(',', $id_str);
        $coupons = Coupon::select('id')->whereBetween('flag', [30, 70])->get();
        $coupon_ids = [];
        foreach ($coupons as $coupon) {
            $coupon_ids[] = $coupon->id;
        }
        foreach ($ids as $id) {
            $member = Member::where('user_id', $id)->first();
            if ($member) {
                $record = MemberCardRecord::where('user_id', $id)
                    ->where('card_type', MemberCardRecord::CARD_TYPE['experience'])
                    ->where('status', MemberCardRecord::STATUS['is_pay'])
                    ->exists();
                // 只处理购买体验卡的用户
                if ($record) {
                    // 查询该用户星球会员相关的券是否被使用
                    $used = CouponLibrary::where('user_id', $id)
                        ->whereIn('coupon_id', $coupon_ids)
                        ->where('status', CouponLibrary::STATUS['used'])
                        ->exists();
                    if (!$used) {
                        // 更改用户会员卡信息 过期时间 会员类型
                        DB::table('members')->where('user_id', $id)->update([
                            'expire_time' => Carbon::now()->subDay(),
                            'member_type' => 0,
                        ]);
                        GiftRecord::where('user_id', $id)
                            ->where('gift_type', GiftRecord::GIFT_TYPE['star_monthly_welfare'])
                            ->whereNull('pick_at')
                            ->delete();
                        CouponLibrary::where('user_id', $id)
                            ->whereIn('coupon_id', $coupon_ids)
                            ->where('status', CouponLibrary::STATUS['surplus'])
                            ->delete();
                    } else {
                        echo 'id = ' . $id . ' 优惠券已使用 ';
                    }
                } else {
                    echo 'id = ' . $id . ' 没有购买体验卡记录 ';
                }
            } else {
                echo 'id = ' . $id . ' 无效    ';
            }
        }
//        $order_no = $this->argument('order_no');  // 获取订单号
//        $record = MemberCardRecord::where('order_no', $order_no)->where('status', MemberCardRecord::STATUS['is_pay'])->first();
//        try {
//            if ($record) {
//                $order_no = $record->order_no;
//                $fee = 100 * $record->price;
//                $app = new WechatController();
//                $refund = $app->refund($order_no, 'REFUND' . $order_no, $fee, $fee);
//                \Log::info('REFUND_MSG = ', [$refund]);
//            }
//        } catch (\Exception $e) {
//            \Log::error('REFUND_ERROR = ', [$e]);
//        }
    }
}
