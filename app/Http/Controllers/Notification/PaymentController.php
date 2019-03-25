<?php

namespace App\Http\Controllers\notification;

use App\Http\Controllers\WechatController;
use App\Models\Coupon;
use App\Models\GiftRecord;
use App\Models\Level;
use App\Models\Member;
use App\Models\MemberCardRecord;
use App\Models\MemberExp;
use App\Models\MemberScore;
use App\Models\Order;
use App\Models\StarLevel;
use App\Models\User;
use App\Services\SyncpoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Log;
use DB;
use IQuery;


class PaymentController extends Controller
{
    /**
     * 支付回调
     */
    public function payment(Request $request)
    {
        $app = new WechatController();
        $response = $app->app->handlePaidNotify(function ($message, $fail) {
            Log::info('wechat_payment_result', [$message]);
            $order = MemberCardRecord::where('order_no', $message['out_trade_no'])->first();
            if (!$order || $order->paid_at) { // 如果订单不存在 或已支付
                return true;
            }
            // 用户是否支付成功
            if ($message['result_code'] === 'SUCCESS') {
                if (!IQuery::redisSetnx('card_notify_' . $order->user_id, 1)) {
                    return $fail('正在处理中');
                }
                IQuery::redisExpire('card_notify_' . $order->user_id, 30);
                DB::beginTransaction();
                try {
                    // 支付成功就执行发券等操作
                    $this->notifySuccess($message['time_end'], $order);
                    DB::commit();
                    Log::info('notify success!');
                } catch (\Exception $e) {
                    Log::error('NOTIFICATION_ERROR MSG = ', [$e]);
                    DB::rollBack();
                    return $fail('处理失败，请稍后再通知我');
                } finally {
                    IQuery::redisDelete('card_notify_' . $order->user_id);
                }
                // 推送订单给同步时
//                try {
//                    $this->sendPos($order);
//                } catch (\Exception $e) {
//                    \Log::error('SEND_POS_ERROR_MSG = ', [$e]);
//                }
                return true; // 返回处理完成
            }
            return $fail('支付验证失败');
        });
        \Log::info('END_DEAL_WECHAT_PAYMENT');
        return $response;
    }

    /**
     * 回调购买成功后操作
     */
    public function notifySuccess($time_end, MemberCardRecord $order)
    {
        $paidAt = isset($time_end) && $time_end ? $time_end : date('Y-m-d H:i:s');
        $member = Member::where('user_id', $order->user_id)->first();
        $expire_time = Carbon::createFromTimestamp(strtotime($member->expire_time));
        // 续费则为时间累加
        if (strtotime($expire_time) >= strtotime(Carbon::today())) {
            $period_start = Carbon::createFromTimestamp(strtotime($expire_time))->addDay();
            $period_end = MemberCardRecord::getPeriodEnd($order->card_type, clone($period_start))->endOfDay();
        } else {
            // 开始时间以付款时间为准
            $period_start = $paidAt;
            $period_end = MemberCardRecord::getPeriodEnd($order->card_type, Carbon::today())->endOfDay();
        }
        $order->paid_at = $paidAt;
        $order->status = MemberCardRecord::STATUS['is_pay'];
        $order->save();
        $this->cardContent($order->card_type, $order->user()->first(), $member, $period_end);
        $price = floor($order->price);
        // 购卡成功发放积分
        createPoint($order->user_id, $price, 'cost', '购买星球会员卡获得', $order->id);
        // 新增经验值记录
        MemberExp::createMemberExp($member, $order->user_id, $order->id, MemberCardRecord::class,
            MemberExp::METHOD['cost'], floor($price/2), $price, '购买星球会员卡获得');
        // 给用户增加经验
        $member->update([
            'star_exp' => $member->star_exp + $price,
            'exp' => $member->exp + floor($price/2)
        ]);
        // 手动调用触发器
        goLevel($member);
        starLevel($member);
        // todo 推送订单给同步时 暂时不启动
//        $this->sendPos($order, $price);
    }

    /**
     * 支付成功后发券操作
     */
    public function cardContent($card_type, User $user, Member $member, $period_end)
    {
        // 判断是否是首充
        $records = $user->memberCardRecord()->where('status', MemberCardRecord::STATUS['is_pay'])
            ->where('card_type', '!=', MemberCardRecord::CARD_TYPE['experience'])
            ->where('card_type', '!=', MemberCardRecord::CARD_TYPE['vka'])
            ->where('paid_type', '!=', 4)->select('id')->count();
        $user_id = $user->id;
        // 卡券包
        createCardCoupon($card_type, $user_id);
        // 生日券  生日在本月 且不存在生日赠饮券  就给用户发一张生日赠饮券 可使用日期为会员生日当天
        // 这里直接用user对象查询的时候竟然没有user_id
        sendBirthday($user_id);
        // 星球会员日赠饮券
        sendPrimeDayCoupon($user_id);
        // 星球会员纪念日券
        sendAnniversaryCoupon($user_id);
        // 发放每月福利
        // 每月福利礼包废除 改为直接发  首月的福利在购卡时发放 其余月份发放礼包 使用定时器触发
        createMonthGift($card_type, $user);
        $member_type = $member->member_type == 0 ? 1 : $member->member_type;
        $star_time = $member->star_time ?? Carbon::now();
        // 购买记录会提前插入 只能查到一条记录的情况下为首充
        if ($records == 1) {
            if ($card_type != MemberCardRecord::CARD_TYPE['experience']) {
                createCoupon('buy_fee_first_1-1', $user_id, 1);
                createCoupon('cash_star_first', $user_id, 2);
            }
        }
        $member->update(['star_time' => $star_time, 'expire_time' => $period_end, 'member_type' => $member_type]);
    }


    /**
     * 推送订单给同步时
     */
    public function sendPos(MemberCardRecord $order)
    {
        $sku_name = DB::table('skus')->where('no', MemberCardRecord::SKU[$order->card_type])->value('name');
        $data = [
            'shopId' => '00000', // 订单所属的门店编号 现默认为深圳总部店
            'ordersNo' => $order->order_no,
            'ordersSeq' => date("Ymd").$order->id, // 流水号
            'ordersTime' => $order->created_at->toDateTimeString(), // 下单时间 or 付款时间
            'totalAmount' => round($order->price, 2), // 订单金额
            'items' => [[
                'skuId' => MemberCardRecord::SKU[$order->card_type],  //商品编号 sku no
                'skuName' => $sku_name,
                'salePrice' => $order->price,
                'qty' => 1,   // 数量
                'saleAmount' => $order->price, // 商品销售金额，不包含加料金额
                'saleSubtotal' => $order->price,   // 单项销售金额合计，包含加料金额
            ]],
            'paidStatus' => 'P'
        ];
        $syn = new SyncpoService();
        $syn->sendPosRequest($data);
    }
}
