<?php
/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/4/09
 * Time: 上午15:49
 * desc: 小程序端优惠券控制器
 */

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Coupon\CouponDiscountRequest;
use App\Http\Requests\Api\Coupon\OrderCouponRequest;
use App\Models\User;
use App\Policies\Policy;
use App\Services\MiniGame;
use App\Transformers\Api\CouponLibraryTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\CouponLibrary;
use App\Models\Member;
use IQuery;
use DB;
use App\Transformers\Api\CouponTransformer;
use App\Transformers\Api\CouponQrcodeTransformer;
use App\Http\Controllers\ApiController;
use App\Models\Coupon;
use App\Models\CouponGrand;
use App\Policies\CouponLibrary\CashCouponPolicy;
use App\Policies\CouponLibrary\FeeCouponPolicy;
use App\Policies\CouponLibrary\QueueCouponPolicy;
use App\Http\Repositories\Api\CouponRepository;

class CouponController extends ApiController
{
    // redis存储用户错误次数
    const REDIS_ERROR = 'laravel:coupon:error:time';
    const REDIS_ERROR_TIME = 'CDKEY_ERROR_TIMES_';
    const REDIS_ERROR_LOCK = 'CDKEY_EXCHANGE_LOCK_';
    /*
     * CODE状态编码
     * errlimit错误10次限制
     * errcode兑换码错误
     * usedcode兑换码已使用
     * expirecode兑换码已过期
     * tenerr错误10次提示
     * finishup喜茶券没有了
     * libexpire喜茶券已过期
     * liblimit喜茶券领取超出限制
     * isnotvip是否是体验用户
     * lowlevel 星球会员等级不够
     * hasexchange 本月已兑换过一次
     * noexchange 不可以兑换，非法操作
     * amountlower 累计单数不够
     * syserr 系统错误
     */
    const CODE = [
        'errlimit' => 1001,
        'errcode' => 4001,
        'usedcode' => 4002,
        'expirecode' => 4003,
        'tenerr' => 4004,
        'finishup' => 4005,
        'libexpire' => 4006,
        'liblimit' => 4007,
        'isnotvip' => 4008,
        'lowlevel' => 4009,
        'hasexchange' => 4010,
        'noexchange' => 4011,
        'amountlower' => 4012,
        'syserr' => 4013,
        'coupon_error' => 4014
    ];

    /**
     * 小程序可使用优惠券
     * @return type
     */
    public function usableCoupon()
    {
        $user = $this->user();
//        if (env('MALL_SWITCH') == 1) {
//            $is_vip = $user->is_vip;
//        } else {
//            $is_vip = 1;
//        }

        //小游戏
        if (env('MINI_GAME_OPEN')) {
            $MiniGame = new MiniGame();
            $MiniGame->getCoupon($user);
        }
        $is_vip = 1;
        $rps = new CouponRepository();
        $library = $rps->usableCoupon($user);
        return $this->response->collection($library, new CouponTransformer(), ['is_vip' => $is_vip]);
    }

    /**
     * 小程序已使用或已过期优惠券
     * @return type
     */
    public function usedAndPeriodCoupon(Request $request)
    {
        $user = $this->user();
        $type = $request->type;
        $rps = new CouponRepository();
        $library = $rps->usedAndPeriodCoupon($user, $type);
        return $this->response->collection($library, new CouponTransformer());
    }

    /**
     *订单优惠券
     */
    public function orderCoupon(OrderCouponRequest $request)
    {
        $user = $this->user();
        $rps = new CouponRepository();
        $res = $rps->orderCoupon($user, $request);
        return $this->response->collection($res['librarys'], new CouponLibraryTransformer(), ['total' => $res['total']]);
    }

    /**
     * 计算订单使用优惠券减多少钱
     */
    public function couponDiscount(CouponDiscountRequest $request)
    {
        $items = $request->get('items');
        $shop_id = $request->get('shop_id');
        $is_take = $request->get('is_take');
        $delivery_fee = $request->get('delivery_fee');
        $coupon_ids = explode(',', $request->get('coupon_library_id'));
        $flag = 0;
        foreach ($coupon_ids as $coupon_id) {
            $couponLibrary = CouponLibrary::with('coupon', 'coupon.product', 'coupon.category', 'coupon.material', 'coupon.category.products')->findOrFail(intval($coupon_id));
            if ($couponLibrary->policy != QueueCouponPolicy::class) {
                $libraryPolicy = app($couponLibrary->policy);
                $flag = 1;
                break;
            }
        }
        if (!$flag) {
            $discount = 0;
        } else {
            if (!$libraryPolicy->verifyItems($items)) return error_return(9706); //item数据格式错误
            $discount = $libraryPolicy->discount($couponLibrary, $items, $shop_id, $delivery_fee);
        }
        return response()->json(['code' => 0, 'result' => $discount]);
    }
    
    /**
     * 根据外卖时间段返回外卖配送费
     * coupon_library_id优惠券id
     * delivery_fee配送费
     */
    public function takeoutFee(Request $request) {
        $coupon_library_id = $request->get('coupon_library_id');
        $delivery_fee = $request->get('delivery_fee');
        $library = CouponLibrary::findOrFail($coupon_library_id);
        if ($library->policy != FeeCouponPolicy::class) {
            return response()->json(['code' => self::CODE['coupon_error'], 'result' => []]);
        }
        $policy_rule = $library->policy_rule;
        if ($policy_rule['cup_type'] != CouponLibrary::CUPTYPE['take']) {
            return response()->json(['code' => self::CODE['coupon_error'], 'result' => []]);
        }
        $result['id'] = $library->id;
        $result['discount'] = $delivery_fee;
        $result['name'] = $library->name;
        $result['type_num'] = 1;   //赠饮券的类型数据
        return response()->json(['code' => 0, 'result' => $result]);
    }

    /*
     * 小程序端响应
     * code:
     * 1001灰置
     * 0恢复提交设置
     */
    public function checkPop(Request $request)
    {
        $user = $this->user();
        $error_time = IQuery::redisGet(self::REDIS_ERROR . $user->id);
        if ($error_time == 10) {
            return response()->json(['code' => self::CODE['errlimit']]);
        }
        return success_return();
    }

    /*
     * 线下优惠券兑换接口
     * code：
     * 0表示兑换成功
     * 4001兑换码错误
     * 4002兑换码已被使用
     * 4003兑换码已过期
     * 4004输入错误10次
     */
    public function codeExchange(Request $request)
    {
        $user = $this->user();
        $code = strtoupper(trim($request->code));  //将小写字母全转为大写
        DB::beginTransaction();
        try {
            $lock = IQuery::redisGet(self::REDIS_ERROR_LOCK . $user->id);
            if ($lock) {
                return response()->json(['code' => self::CODE['tenerr'], 'msg' => '兑换码错误,请1小时后再输入']);
            }
            $library = CouponLibrary::where('code', $code)->lockForUpdate()->first();
            if (!$library) {
                $error_time = IQuery::redisGet(self::REDIS_ERROR_TIME . $user->id);
                if ($error_time >= 4) {
                    IQuery::redisSet(self::REDIS_ERROR_LOCK . $user->id, 1, 3600);
                }
                if (!isset($error_time)) {
                    IQuery::redisSet(self::REDIS_ERROR_TIME . $user->id, 0, 3600);
                } else {
                    IQuery::redisIncr(self::REDIS_ERROR_TIME . $user->id);
                }
                DB::commit();
                return response()->json(['code' => self::CODE['errcode'], 'msg' => '兑换码错误']);
            }
            switch ($library->status) {
                case CouponLibrary::STATUS['unpick']:
                    $library->user_id = $user->id;
                    $library->status = CouponLibrary::STATUS['surplus'];
                    $library->created_at = Carbon::now();
                    $library->tab = CouponLibrary::NEWTAB['new'];  //1为新优惠券标识
                    $coupon = $library->coupon;
                    if ($coupon->period_type) {
                        $library->period_start = Carbon::today()->startOfDay();
                        if ($coupon->unit_time == 1) {
                            $library->period_end = Carbon::today()->addMonths($coupon->period_day)->endOfDay();
                        } else if ($coupon->unit_time == 2) {
                            $library->period_end = Carbon::today()->addYears($coupon->period_day)->endOfDay();
                        } else {
                            $library->period_end = Carbon::today()->addDays($coupon->period_day)->endOfDay();
                        }
                    } else {
                        $library->period_start = $coupon->period_start; 
                        $library->period_end = $coupon->period_end;
                    }
                    $library->save();
                    $user->members()->update(['new_coupon_tab' => Member::NEWTAB['new']]);
                    DB::commit();
                    return success_return();
                case CouponLibrary::STATUS['surplus']:
                case CouponLibrary::STATUS['used']:
                    DB::commit();
                    return response()->json(['code' => self::CODE['usedcode'], 'msg' => '兑换码已被使用']);
                case CouponLibrary::STATUS['period']:
                    $result = !$library->user_id ? ['code' => self::CODE['expirecode'], 'msg' => '兑换码已过期'] : ['code' => self::CODE['usedcode'], 'msg' => '兑换码已被使用'];
                    DB::commit();
                    return response()->json($result);
                default:
                    DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::info('EXCHANGE_ERROR', [$user->name, $e]);
        }
    }

    /*
     * 扫码二维码优惠券前端显示
     * 4005喜茶券被全部领取
     * 4006喜茶券已过期
     * 4007超出限制
     * 4008黑名单不具备权限
     */
    public function qrCodeShow(Request $request)
    {
        $this->validate($request, [
            'coupon_id' => 'required|integer'
        ]);
        $user = $this->user();
        $grand_id = $request->coupon_id;
//        if (env('MALL_SWITCH') == 1) {
//            if (!$user->is_vip) {
//                return response()->json(['code' => self::CODE['isnotvip']]);
//            }
//        }
        $grand = CouponGrand::findOrFail($grand_id);
        $count = $grand->count;
        $library = $grand->library()->where('status', CouponLibrary::STATUS['unpick'])->first();
        if (!$library) {
            $lib = $grand->library()->first();
            $lib->errorno = self::CODE['finishup'];
            $lib->count = $count;
            if ($grand->library()->where('user_id', $user->id)->count() >= $grand->count) {
                $lib->errorno = self::CODE['liblimit'];
            }
            return $this->response->item($lib, new CouponQrcodeTransformer());
        }
        if ($grand->library()->where('user_id', $user->id)->count() >= $count) {
            $library->errorno = self::CODE['liblimit'];
        }
        if ($library->period_end->format('Y-m-d') < Carbon::today()->format('Y-m-d')) {
            $library->errorno = self::CODE['libexpire'];
        }
        $library->count = $count;
        return $this->response->item($library, new CouponQrcodeTransformer());
    }

    /*
     * 二维码兑换
     * 4005喜茶券被全部领取
     * 4006喜茶券已过期
     * 4007超出限制
     * 4008黑名单不具备权限
     */
    public function qrCodeExchange(Request $request)
    {
        $this->validate($request, [
            'coupon_id' => 'required|integer'
        ]);
        $user = $this->user();
        $grand_id = $request->coupon_id;
//        if (env('MALL_SWITCH') == 1) {
//            if (!$user->is_vip) {
//                return response()->json(['code' => self::CODE['isnotvip'], 'msg' => '不具备兑换权限']);
//            }
//        }
        DB::beginTransaction();
        try {
            $grand = CouponGrand::lockForUpdate()->findOrFail($grand_id);
            $count = $grand->count;
            $arrIds = $grand->library()->where('status', CouponLibrary::STATUS['unpick'])->orderBy('id', 'asc')->offset(0)->limit($count)->pluck('id');
            if (count($arrIds) == 0) {
                DB::commit();
                return response()->json(['code' => self::CODE['finishup'], 'msg' => '喜茶券被全部领取']);
            }
            if ($grand->library()->first()->period_end->format('Y-m-d') < Carbon::today()->format('Y-m-d')) {
                DB::commit();
                return response()->json(['code' => self::CODE['libexpire'], 'msg' => '喜茶券已过期']);
            }
            if ($grand->library()->where('user_id', $user->id)->count() >= $grand->count) {
                DB::commit();
                return response()->json(['code' => self::CODE['liblimit'], 'msg' => '喜茶券超出限制']);
            }
            $grand->library()->whereIn('id', $arrIds)->update([
                'user_id' => $user->id,
                'status' => CouponLibrary::STATUS['surplus'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'tab' => CouponLibrary::NEWTAB['new']
            ]);
            $user->members()->update(['new_coupon_tab' => Member::NEWTAB['new']]);
            DB::commit();
            return success_return();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::info('CODECHANGE_ERROR', [$user->name, $e]);
        }
    }

    /**
     * 星球会员 钻石等级以上 每xx单可以领取一张赠饮券
     *
     * 领取满单赠饮券
     *  钻石会员 20单领取一张 单月一张
     *  黑金会员 10单领取一张 单月两张
     *  黑钻会员 5单领取一张  单月三张
     */
    public function getStarRedeemedCouponStatus()
    {
        if ($this->isDiamond()) {
            $data = [
                'cut_num' => 0, // 累计杯数
//                'has_exchange' => true, // 本月是否兑换过
                'enough' => 20,     // 满xx杯才可兑换
                'status' => false   // 是否可以兑换
            ];
            // 累计单数
            $member = Member::where('user_id', $this->user()->id)->select(['id', 'star_level_id', 'member_cup'])->first();
            $data['cut_num'] = $member->member_cup;

            if ($this->cpuEnough($member->member_cup, $member->starLevel->name, $data['enough'])) {
                $data['status'] = true;
            }
            return response()->json($data);
        }
        return response()->json(['code' => self::CODE['lowlevel'], 'msg' => '加油去升级星球会员等级吧']);
    }

    /**
     * 星球会员满xx单兑换优惠券
     */
    public function exchangeCoupon(Request $request)
    {
        $enough = 20;
        $member = Member::where('user_id', $this->user()->id)->select(['id', 'star_level_id', 'member_cup'])->first();
        if ($this->isDiamond() && $this->cpuEnough($member->member_cup, $member->starLevel->name, $enough)) {
            DB::beginTransaction();
            try {
                // 累计数量减少
                $member->decrement('member_cup', $enough);
                // 根据等级判断出券模板
                $flag = 'fee_star_' . $enough;
                // 找到券模板id
                $coupons = Coupon::where('flag', Coupon::FLAG[$flag])->orderBy('updated_at', 'desc')->select('id')->first();
                // 找到最新的3张券 黑钻会员每月可用3张 黑金2张 钻石1张
                $record = CouponLibrary::where('user_id', $this->user()->id)->where('coupon_id', $coupons->id)
                    ->where('period_end', '>=', Carbon::today()->startOfMonth())
                    ->select(['id', 'period_start', 'period_end'])->orderBy('period_start', 'desc')->limit(3)->get();
                // 根据身份判断最新月的券数量
                switch ($member->starLevel->name) {
                    case '钻石':
                        if ($record->count()) {
                            // 存在1条记录就创建一张最新券的下个月的券
                            createCoupon($flag, $this->user()->id, 1, $record[0]->period_start->addMonth()->endOfMonth(),
                                $record[0]->period_start->addMonth()->startOfMonth());
                        } else {
                            // 不存在就创建一条本月的记录
                            createCoupon($flag, $this->user()->id, 1, Carbon::today()->endOfMonth(), Carbon::now());
                        }
                        break;
                    case '黑金':
                        if (($record->count() >= 2) && ($record[0]->period_end == $record[1]->period_end)) {
                            // 存在2条以上记录  2条记录结束日期相同就创建一张最新券的下个月的券 不同就复制最新的券
                            createCoupon($flag, $this->user()->id, 1, $record[0]->period_start->addMonth()->endOfMonth(),
                                $record[0]->period_start->addMonth()->startOfMonth());
                        } else if (!$record->count()) {
                            // 不存在就创建一条本月的记录
                            createCoupon($flag, $this->user()->id, 1, Carbon::today()->endOfMonth(), Carbon::now());
                        } else {
                            // 存在一条记录 以及 2张不同的券时  就复制该张券
                            $period_start = $record->count() > 2 ? $record[0]->period_start : Carbon::now();
                            createCoupon($flag, $this->user()->id, 1, $record[0]->period_end, $period_start);
                        }
                        break;
                    case '黑钻':
                        if ((3 == $record->count() && (($record[0]->period_end == $record[1]->period_end) && (($record[1]->period_end == $record[2]->period_end))))) {
                            // 存在3条记录 3条记录结束日期相同就创建一张最新券的下个月的券 不同就复制最新的券
                            createCoupon($flag, $this->user()->id, 1, $record[0]->period_start->addMonth()->endOfMonth(),
                                $record[0]->period_start->addMonth()->startOfMonth());
                        } else if (!$record->count()) {
                            // 不存在就创建一条本月的记录
                            createCoupon($flag, $this->user()->id, 1, Carbon::today()->endOfMonth(), Carbon::now());
                        } else {
                            // 存在1条或者2条记录 以及 3张不同的券时 就复制最新的券
                            createCoupon($flag, $this->user()->id, 1, $record[0]->period_end, $record[0]->period_start);
                        }
                        break;
                }
                DB::commit();
                return response()->json(['code' => 0, 'msg' => '领取成功']);
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('STAR_REDEEMED_COUPON_ERROR', [$this->user()->name, $e]);
            }
        }
        return response()->json(['code' => 4001, 'msg' => '暂时无法兑换']);
    }


    // 是否是星球会员且等级达到钻石及以上
    public function isDiamond()
    {
        $member = Member::where('user_id', $this->user()->id)->where('expire_time', '>=', Carbon::today())
            ->select(['id', 'expire_time', 'star_level_id'])->first();
        // 是否是星球会员
        if (isset($member)) {
            // 会员等级是否大于钻石
            $star_level = $member->starLevel()->whereIn('name', ['钻石', '黑金', '黑钻'])->exists();
            return $star_level;
        }
        return false;
    }


    // 累计单数是否可以兑换
    public function cpuEnough($cup_num, $star_level, &$enough)
    {
        switch ($star_level) {
            case '钻石':
                $enough = 20;
                return $cup_num >= 20;
            case '黑金':
                $enough = 10;
                return $cup_num >= 10;
            case '黑钻':
                $enough = 5;
                return $cup_num >= 5;
            default:
                return false;
        }
    }

}
