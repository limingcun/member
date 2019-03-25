<?php
/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/4/09
 * Time: 下午16:55
 * desc: 小程序端优惠券控制器
 */

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\MemberScore;
use App\Models\ScoreRule;
use App\Models\Member;
use App\Transformers\Api\MemberScoreTransformer;
use Log;
use DB;
use IQuery;
use App\Models\Order;
use App\Models\CouponLibrary;
use App\Http\Controllers\ApiController;
use App\Transformers\Api\PointTransformer;
use App\Services\JiPush;
use App\Models\MemberExp;
use App\Models\Shop;
use App\Models\Message;

class PointController extends ApiController
{
    /*
     * 小程序端获取会员积分
     */
    public function getPoint() {
        $user = $this->user();
        $point = $user->score()->with('source')->orderBy('id', 'desc')->paginate(10);
        return $this->response->collection($point, new PointTransformer());
    }

    /*
     * 获取全部积分、可用积分和到期积分
     */
    public function getAllPoint() {
        $user = $this->user();
        $member = $user->members()->select('usable_score', 'score_lock')->first();  //从会员表获取可用积分
        return response()->json(['usable_score' => $member->usable_score, 'score_lock' => $member->score_lock]);
    }

    /*
     * 会员积分规则
     */
    public function getRule() {
        $res = ScoreRule::first();
        return response()->json($res);
    }

    /*
     * 同步时退款
     */
    public function syncRefundApi(Request $request) {
        $this->validate($request, [
            'order_id' => 'required|integer'
        ]);
        $order_id = $request->order_id;
        $order = Order::find($order_id);
        $user_id = $order->user_id;
        if (MemberScore::where('user_id', $user_id)->where('method', MemberScore::METHOD['refund'])->where('source_id', $order_id)->where('source_type', Order::class)->first()) {
            return;
        }
        $member = Member::where('user_id', $user_id)->first();
        DB::beginTransaction();
        try{
            $score_change = MemberScore::where('user_id', $user_id)->where('source_id', $order_id)
                ->where('source_type', Order::class)->sum('score_change');
            $this->saveMemberScore($member, $score_change, $order);
            $dataArr = $this->memberData($member, $order);
            //保存member表数据
            $member = $this->saveMember($member, $score_change, $order, $dataArr);
            $this->saveMemberExp($member, $order, $dataArr);
            $this->markToMemberScore($order_id, $user_id);
            //app触发极光推送
            if ($order->trade_type == 'APP' || $order->trade_type == 'IPAY') {
                $this->appPush($order, $user_id);
            }
            DB::commit();
            Log::info('sync_refund_success', ['sync_success']);
            return success_return();
        }
        catch (\Exception $exception) {
            //捕获处理异常并回滚
            DB::rollback();
            Log::info('sync_refund_error', [$exception]);
            return error_return(1802);
        }
    }

    /*
     * 退款积分扣除录入
     * order_id 购买订单id
     */
    public function decScore(Request $request) {
        $this->validate($request, [
            'order_id' => 'required|integer'
        ]);
        $order_id = $request->order_id;
        $order = Order::find($order_id);
        $user_id = $order->user_id;
        if ($order->trade_type == 'POS') {
            return;
        }
        if (MemberScore::where('user_id', $user_id)->where('method', MemberScore::METHOD['refund'])->where('source_id', $order_id)->where('source_type', Order::class)->first()) {
            return;
        }
        $cost = MemberScore::where('user_id', $user_id)->where('method', MemberScore::METHOD['cost'])->where('source_id', $order_id)->where('source_type', Order::class)->first();
        if (!$cost) {
            return;
        }
        $member = Member::where('user_id', $user_id)->first();
        if ($member->score_lock) {
            return;
        }
        DB::beginTransaction();
        try{
            $score_change = MemberScore::where('user_id', $user_id)->where('source_id', $order_id)
                ->where('source_type', Order::class)->sum('score_change');
            if ($order->coupon_library_id) {
                //订单交易完成不退券
                if (!$order->is_takeaway) {
                    if (!$order->called_at) {
                        $this->reBackLibrary($order->coupon_library_id);
                        Message::couponsReturnMsg($member->user_id);
                        $member->update(['message_tab' => $member->message_tab + 1]);
                    }
                } else {
                    $delivery_status = $order->delivery->delivery_status ?? '';
                    if (!in_array($delivery_status, ['DISPATCHED', 'CLOSED'])) {
                        $this->reBackLibrary($order->coupon_library_id);
                        Message::couponsReturnMsg($member->user_id);
                        $member->update(['message_tab' => $member->message_tab + 1]);
                    }
                }
            }
            $this->saveMemberScore($member, $score_change, $order);
            $dataArr = $this->memberData($member, $order);
            //保存member表数据
            $member = $this->saveMember($member, $score_change, $order, $dataArr);
            $this->saveMemberExp($member, $order, $dataArr);
            $this->markToMemberScore($order_id, $user_id);
            //app触发极光推送
            if ($order->trade_type == 'APP' || $order->trade_type == 'IPAY') {
                $this->appPush($order, $user_id);
            }
            DB::commit();
            Log::info('dec_score_success', ['dec_success']);
            return success_return();
        }
        catch (\Exception $exception) {
            //捕获处理异常并回滚
            DB::rollback();
            Log::info('dec_score_error', [$exception]);
            return error_return(1802);
        }
    }

    /**
     * 退单时对通过完成这笔订单任务获得的积分作标记
     * @param $order_id
     * @param $user_id
     */
    public function markToMemberScore($order_id, $user_id) {
        // 通过order_id 和 user_id 找到task_id
        // 再通过task_id找到积分记录
        // 对这笔积分记录作标记
        $tasks = DB::table('istore_task_log')->where('user_id', $user_id)
            ->where('order_id', 'like', "%{$order_id}%")
            ->get();
        foreach ($tasks as $task) {
            if ($task) {
                $member_score = MemberScore::where('task_log_id', $task->log_id)->first();
                if ($member_score) {
                    $member_score->status = 1;
                    $member_score->save();
                }
            }
        }
    }

    /*
     * 退单优惠券返回给用户
     * library_id库id
     */
    public function reBackLibrary($library_id) {
        $lib_ids = explode(',', $library_id);
        $coupon_librarys = CouponLibrary::findOrFail($lib_ids);
        foreach ($coupon_librarys as $coupon_library) {
            $coupon_library->status = CouponLibrary::STATUS['surplus'];
            $coupon_library->used_at = null;
            $coupon_library->order_id = 0;
            $coupon_library->discount_fee = 0;
            $coupon_library->save();
        }

    }

    /*
     * 保存memberScore
     * user_id用户id
     * $score_change积分
     * order订单
     */
    public function saveMemberScore($member, $score_change, Order $order) {
        $is_star = Member::isStarMember($member);
        $member_type = 0;
        $origin = 0;
        if ($is_star) {
            $member_type = 1;
        }
        if ($order->trade_type == 'APP' || $order->trade_type == 'IPAY') {
            $origin = 1;
        }
        $member_score = new MemberScore;
        $member_score->user_id = $order->user_id;
        $member_score->source_id = $order->id;
        $member_score->source_type = Order::class;
        $member_score->method = MemberScore::METHOD['refund'];
        $member_score->score_change = $score_change;
        $member_score->description = $order->refund->first()->reason ?? 'pos机退单';
        $member_score->member_type = $member_type;
        $member_score->origin = $origin;
        $member_score->save();
    }

    /*
     * 保存member
     * member会员类
     * $score_change积分
     * $order订单
     * $dataArr经验值数组
     */
    public function saveMember(Member $member, $score_change, Order $order, $dataArr) {
        $score_lock = Member::SCORELOCT['unlock'];
        if (bcsub($member->usable_score, $score_change) < config('app.threshold')) {
            $score_lock = Member::SCORELOCT['lock'];
        }
        if (!$member->score_lock) {
            $member->exp = bcsub($member->exp, floor($dataArr['exp']));
            $member->star_exp = bcsub($member->star_exp, floor($dataArr['star_exp']));
            $member->usable_score = bcsub($member->usable_score, $score_change);
            $member->order_score = bcsub($member->order_score, $score_change);
        }
        $member->order_money = round($member->order_money - $order->payment, 2);
        $member->order_count = bcsub($member->order_count, 1);
        $member->score_lock = $score_lock;
        if ($member->member_cup != 0) {
            $member->member_cup = bcsub($member->member_cup, $dataArr['cup']);
        }
        $member->save();
        return $member;
    }

    /**
     * 保存memberScore
     * $member会员
     * $order订单
     * point积分
     */
    public function saveMemberExp($member, $order, $dataArr) {
        $description = $order->refund->first()->reason ?? 'pos机退单';
        MemberExp::createMemberExp($member, $order->user_id, $order->id, Order::class, MemberExp::METHOD['refund'], floor($dataArr['exp']), floor($dataArr['star_exp']), $description);
    }

    /**
     * 判断星球会员或go会员
     * $member会员数据
     * $order订单
     */
    public function memberData(Member $member, Order $order) {
        $is_star = Member::isStarMember($member);
        if ($is_star) {
            $exp_rate = 2;
            $star_exp_rate = 1;
        } else {
            $exp_rate = 2;
            $star_exp_rate = 0;
        }
//        $payment = round($order->total_fee - $order->delivery_fee - $order->discount_fee, 2);
        if ($order->payment >= $order->delivery_fee) {
            $payment = round($order->payment - $order->delivery_fee, 2);
        } else {
            $payment = round($order->payment, 2);
        }
        $exp = bcdiv($payment, $exp_rate);
        if (!$star_exp_rate) {
            $star_exp = 0;
        } else {
            $star_exp = bcdiv($payment, $star_exp_rate);
        }
        $cup = $this->memberCup($member, $order);
        return ['exp' => $exp, 'star_exp' => $star_exp, 'cup' => $cup];
    }

    /**
     *
     * 钻石、黑金、黑钻累计杯数
     * 使用赠饮券不算有效杯
     * 商品单杯价格大于11元才算有效杯
     */
    public function memberCup(Member $member, Order $order) {
        $items = $order->item;
        $cup = 0;
        $coupon_flag = 0;
        if ($order->coupon_library_id) {
            $lib_ids = explode(',', $order->coupon_library_id);
            $coupon_librarys = CouponLibrary::findOrFail($lib_ids);
            foreach($coupon_librarys as $coupon_library) {
                $libraryPolicy = app($coupon_library->policy);
                $type_num = $libraryPolicy->typeNum();
                if ($type_num == 1) {  //使用赠饮券不算有效单
                    $coupon_flag = 1;
                    break;
                }
            }
        }
        if ($coupon_flag) {
            return $cup;
        }
        $price_flag = 0;
        foreach($items as $item) {
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

    /**
     * app极光推送
     * $order订单
     * $user_id用户id
     */
    public function appPush($order, $user_id)
    {
        $jpush = new JiPush;
        if ($order->shop->city_code != Shop::HK_CITY_CODE) {
            $alert = '您的退款已申请成功，请耐心等候退款结果';
        } else {
            $alert = '您的退款已申請成功，請耐心等候退款結果';
        }
        $jpush->sendAppMsg((string) $order->user_id, $alert);
    }
}
