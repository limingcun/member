<?php
/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/4/09
 * Time: 上午15:49
 * desc: 个人首页控制器
 */

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Services\MiniGame;
use Illuminate\Http\Request;
use App\Transformers\Api\UserTransformer;
use App\Models\Member;
use App\Models\CouponLibrary;
use Carbon\Carbon;
use App\Models\User;
use DB;
use App\Models\Active;
use App\Models\ScoreRule;
use App\Models\MemberScore;
use App\Models\MemberExp;
use App\Services\JiPush;
use App\Models\Shop;
use IQuery;
use Log;
use App\Models\Coupon;
use App\Policies\CouponLibrary\CashCouponPolicy;
use App\Policies\CouponLibrary\QueueCouponPolicy;
use App\Jobs\CommentQueueJob;

class IndexController extends Controller
{
    /*
     * 获取积分和优惠券数量
     */
    public function getPointAndCouponNum(Request $request)
    {
        $user = $this->user();
        $is_vip = 1;
        $point = $user->members()->select('usable_score', 'new_coupon_tab', 'score_lock')->first();
        $coupon = $user->library()->where('period_end', '>=', Carbon::today())
            ->where('status', CouponLibrary::STATUS['surplus'])
            ->select(DB::raw('count(1) as coupon_num'))->first();
        return response(compact('point', 'coupon', 'is_vip'));
    }

    /**
     * 同步时支付接口
     */
    public function syncBuyApi(Request $request)
    {
        Log::info('HEYUJIA', [$request->order_id]);
        $this->validate($request, [
            'order_id' => 'required|integer'
        ]);
        $order_id = $request->order_id;
        $order = Order::find($order_id);
        $user_id = $order->user_id;
        $member = Member::where('user_id', $user_id)->first();
        if ($member->score_lock) {
            return 'fail';
        }
        if (MemberScore::where('user_id', $user_id)->where('method', MemberScore::METHOD['cost'])->where('source_id', $order_id)->where('source_type', Order::class)->first()) {
            return 'fail';
        }
        DB::beginTransaction();
        try {
            $dataArr = $this->memberData($member, $order);  //星球会员获取积分和经验值
            //保存memberScore
            $this->saveMemberScore($member, $order, $dataArr['point']);  //保存消费获取积分
            $dataArr = $this->extraPoint($member, $order, $dataArr); //会员纪念日获得额外积分
            //优惠券核销
            $coupon_flag = 0;
            if ($order->coupon_library_id) {
                $coupon_flag = $this->couponLibraryUsed($order);
            }
            $cup = $this->memberCup($member, $order, $coupon_flag);  //钻石会员、黑金会员、黑钻会员累计杯数
            //保存member表数据信息
            $member = $this->saveMember($member, $order, $user_id, $dataArr, $cup);
            $this->saveMemberExp($member, $order, $dataArr);
            //app触发极光推送
            if ($order->trade_type == 'APP' || $order->trade_type == 'IPAY') {
                $this->appPush($order, $user_id);
            }
            DB::commit();
            Log::info('sync_score_success', ['sync_success']);
            return response()->json('success');
        } catch (\Exception $exception) {
            //捕获并处理异常并回滚
            DB::rollback();
            Log::info('sync_score_error', [$exception]);
            return response()->json('error');
        }
    }

    /*
     * 积分补录，优惠券核销，新用户活动领券公共接口
     */
    public function commonApi(Request $request)
    {
        $this->validate($request, [
            'order_id' => 'required|integer'
        ]);
        $order_id = $request->order_id;
        $order = Order::find($order_id);
        if ($order->trade_type == 'POS') {
            return;
        }
        $user_id = $order->user_id;
        $member = Member::where('user_id', $user_id)->first();
        if ($member->score_lock) {
            return;
        }
        if (MemberScore::where('user_id', $user_id)->where('method', MemberScore::METHOD['cost'])->where('source_id', $order_id)->where('source_type', Order::class)->first()) {
            return;
        }
        //同步核销券,异步跑队列
//        $coupon_flag = 0;
//        if ($order->coupon_library_id) {
//            $coupon_flag = $this->couponLibraryUsed($order);
//        }
//        CommentQueueJob::dispatch($order_id, $coupon_flag);
        DB::beginTransaction();
        try {
            $dataArr = $this->memberData($member, $order);  //星球会员获取积分和经验值
            //保存memberScore
            $this->saveMemberScore($member, $order, $dataArr['point']);  //保存消费获取积分
            $dataArr = $this->extraPoint($member, $order, $dataArr); //会员纪念日获得额外积分
            //优惠券核销
            $coupon_flag = 0;
            if ($order->coupon_library_id) {
                $coupon_flag = $this->couponLibraryUsed($order);
            }
            $cup = $this->memberCup($member, $order, $coupon_flag);  //钻石会员、黑金会员、黑钻会员累计杯数
            //保存member表数据信息
            $this->saveMember($member, $order, $user_id, $dataArr, $cup);
            $this->saveMemberExp($member, $order, $dataArr);  //保存经验值
            //app触发极光推送
            if ($order->trade_type == 'APP' || $order->trade_type == 'IPAY') {
                $this->appPush($order, $user_id);
            }
            DB::commit();
            Log::info('inc_score_success', ['inc_success']);
        } catch (\Exception $exception) {
            //捕获并处理异常并回滚
            DB::rollback();
            Log::info('inc_score_error', [$exception]);
        }
    }

    /**
     * 判断星球会员或go会员
     * $member会员数据
     * $order订单
     */
    public function memberData(Member $member, Order $order)
    {
        $is_star = Member::isStarMember($member);
        if ($is_star) {
            $point_rate = 1;
            $exp_rate = 2;
            $star_exp_rate = 1;
        } else {
            $point_rate = 2;
            $exp_rate = 2;
            $star_exp_rate = 0;
        }
        // todo
        $payment = round($order->total_fee - $order->delivery_fee - $order->discount_fee, 2);
//        if ($order->payment >= $order->delivery_fee) {
//            $payment = round($order->payment - $order->delivery_fee, 2);
//        } else {
//            $payment = round($order->payment, 2);
//        }
        $point = bcdiv($payment, $point_rate);
        $exp = bcdiv($payment, $exp_rate);
        if (!$star_exp_rate) {
            $star_exp = 0;
        } else {
            $star_exp = bcdiv($payment, $star_exp_rate);
        }
        return ['point' => $point, 'exp' => $exp, 'star_exp' => $star_exp];
    }

    /**
     * 会员纪念日获得额外积分
     * @param Member $member
     * @param type $dataArr
     */
    public function extraPoint(Member $member, Order $order, $dataArr)
    {
        $is_star = Member::isStarMember($member);
        if ($is_star) {
            if (Carbon::today()->format('d') == 7) {
                $extra_point = $dataArr['point'] * $member->star_level_id * 0.05;
                $dataArr['point'] += $extra_point;
                $this->createExpraScore($order, $extra_point);
            }
        }
        return $dataArr;
    }

    /**
     * 创建额外积分记录
     */
    public function createExpraScore(Order $order, $extra_point)
    {
        MemberScore::create([
            'user_id' => $order->user_id,
            'source_id' => $order->id,
            'source_type' => Order::class,
            'score_change' => floor($extra_point),
            'method' => MemberScore::METHOD['star_date'],
            'description' => '会员纪念日额外赠送积分',
            'member_type' => 1
        ]);
    }

    /**
     *
     * 钻石、黑金、黑钻累计杯数
     * 使用赠饮券不算有效杯
     * 商品单杯价格大于11元才算有效杯
     */
    public function memberCup(Member $member, Order $order, $coupon_flag)
    {
        $items = $order->item;
        $cup = 0;
        $price_flag = 0;
        if ($coupon_flag) {
            return $cup;
        }
        foreach ($items as $item) {
            if ($item->price >= 11) {
                $price_flag = 1;
                break;
            }
        }
        if (!$price_flag) {
            return $cup;
        }
        $is_star = Member::isStarMember($member);
        if ($is_star) {
            if ($member->star_level_id >= 4) {
                $cup = 1;
            }
        }
        return $cup;
    }

    /*
     * 优惠券核销
     * library_id库id
     * order_id订单id
     * items项目类
     */
    public function couponLibraryUsed($order)
    {
        $lib_ids = explode(',', $order->coupon_library_id);
        $coupon_flag = 0;
        foreach ($lib_ids as $lib_id) {
            $library = CouponLibrary::find($lib_id);
            $library->order_id = $order->id;
            if ($library->policy != QueueCouponPolicy::class) {
                $library->discount_fee = $order->discount_fee;
            } else {
                //优先券计算插队数量
                $prior = Order::where('prior', 0)
                    ->where('shop_id', $order->shop_id)
                    ->where('created_at', '>', Carbon::today())
                    ->whereIn('status', ['BUYER_PAY', 'WAIT_SELLER_SEND_GOODS'])
                    ->count();
                Order::where('id', $order->id)->update([
                    'prior' => max($prior, 1)
                ]);
            }
            $libraryPolicy = app($library->policy);
            $type_num = $libraryPolicy->typeNum();
            if ($type_num == 1) {  //使用赠饮券不算有效单
                $coupon_flag = 1;
            }
            $library->status = 2;
            $library->used_at = $order->paid_at;
            $library->save();
        }
        return $coupon_flag;
    }

    /*
     * 保存memberScore
     * $member会员
     * $order订单
     * point积分
     */
    public function saveMemberScore($member, $order, $point)
    {
        $is_star = Member::isStarMember($member);
        $member_type = 0;
        $origin = 0;
        if ($is_star) {
            $member_type = 1;
        }
        if ($order->trade_type == 'APP' || $order->trade_type == 'IPAY') {
            $origin = 1;
        }
        $user_id = $order->user_id;
        $items = $order->item;
        $name = $items->first()->name;
        $num = $items->sum('quantity');
        $description = "购买 {$name} 等{$num}件商品";
        MemberScore::create([
            'user_id' => $user_id,
            'source_id' => $order->id,
            'source_type' => Order::class,
            'method' => MemberScore::METHOD['cost'],
            'score_change' => floor($point),
            'description' => $description,
            'member_type' => $member_type,
            'origin' => $origin
        ]);
    }

    /*
     * 保存member
     * member会员类
     * user_id用户id
     * point积分
     * money金额
     */
    public function saveMember(Member $member, Order $order, $user_id, $dataArr, $cup)
    {
        $library = CouponLibrary::where('user_id', $user_id)->where('status', '!=', CouponLibrary::STATUS['period'])->where('tab', CouponLibrary::NEWTAB['new'])->first();
        if (!$library) {
            $member->new_coupon_tab = Member::NEWTAB['scan'];
        }
        if (!$member->score_lock) {
            $member->exp += floor($dataArr['exp']);
            $member->star_exp += floor($dataArr['star_exp']);
            $member->usable_score += floor($dataArr['point']);
            $member->order_score += floor($dataArr['point']);
        }
        $member->order_money = round($member->order_money + $order->payment, 2);
        $member->order_count = bcadd($member->order_count, 1);
        $member->member_cup = bcadd($member->member_cup, $cup);
        $member->save();
        return $member;
    }

    /**
     * 获取商品描述
     * $order商品订单
     */
    public function description($order)
    {
        $items = $order->item;
        $name = $items->first()->name;
        $num = $items->sum('quantity');
        $description = "购买 {$name} 等{$num}件商品";
        return $description;
    }
    
    /**
     * 保存memberExp
     * $member会员
     * $order订单
     * point积分
     */
    public function saveMemberExp($member, $order, $dataArr)
    {
        $description = $this->description($order);
        MemberExp::createMemberExp($member, $order->user_id, $order->id, Order::class, MemberExp::METHOD['cost'], floor($dataArr['exp']), floor($dataArr['star_exp']), $description);
    }

    /**
     * app极光推送
     * $order订单
     * $user_id用户id
     */
    public function appPush($order, $user_id)
    {
        $jpush = new JiPush;
        if (!$order->is_takeaway) {
            if ($order->shop->city_code != Shop::HK_CITY_CODE) {
                $alert = '已接到您的订单，请留意手机等候取茶通知，前往门店取茶';
            } else {
                $alert = '已接到您的訂單，請留意手機顯示取茶通知，前往門店取茶';
            }
        } else {
            if ($order->shop->city_code != Shop::HK_CITY_CODE) {
                $alert = '已接到您的外卖订单，请等候配送';
            } else {
                $alert = '已接到您的外賣訂單，請等候配送';
            }
        }
        $jpush->sendAppMsg((string)$user_id, '【' . $order->shop->name . '】' . $alert);
    }
}
