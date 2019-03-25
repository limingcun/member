<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Member;
use App\Models\MemberCardRecord;
use App\Models\StarLevel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use IQuery;
use App\Support\Response;
use App\Support\Parameters;
use App\Http\Controllers\Api\CouponController;


class StarCDKEYController extends ApiController
{

    const ERROR_CODE = [
        'cant_renew'        => 2001, // 星球会员有效期还有15天以上
        'used'              => 2002, // 兑换码已使用
        'overdue'           => 2003, // 兑换码已过期
        'not_start'         => 2004, // 兑换码暂未生效
        'ineffective'       => 2005, // 兑换码输入错误
        'sys_err'           => 2006, // 系统处理失败  网络错误
        'first_lock'        => 2007, // 首次锁住
        'lock'              => 2008, // 锁住后再次失败请求
    ];

    /**
     * 兑换会员卡或喜茶券
     * @param Request $request
     */
    public function cdKeyOrCoupon(Request $request, Response $response, Parameters $parameters) {
        $code = $request->code;
        $len = strlen($code);
        if (($len == 17 || $len == 18) && substr($code, 0, 2) == 'HY') {
            return $this->CDKEY($request);
        } else {
            $coupon = new CouponController($response, $parameters);
            return $coupon->codeExchange($request);
        }
    }

        /**
     * 检查兑换码
     */
    public function CDKEY(Request $request)
    {
        $code = $request['code'] ?? false;
        // 每小时只能提交5次
        if ($code) {
            $user = $this->user();
            $error_time = IQuery::redisGet('CDKEY_ERROR_TIMES_' . $user->id);
            if (!$error_time) {
                $error_time = 0;
            }
            $lock = IQuery::redisGet('CDKEY_EXCHANGE_LOCK_' . $user->id);
            // 用户兑换状态锁住的情况 不允许兑换
            if ($lock) {
                return response()->json(['code' => self::ERROR_CODE['lock'], 'msg' => '']);
            }
            if ($error_time >= 4) {
                // 每小时第六次兑换错误时，提示用户1小时候再试 第6次及以后 提示用户稍后再试
                IQuery::redisSet('CDKEY_EXCHANGE_LOCK_' . $user->id, 1, 3600);
                return response()->json(['code' => self::ERROR_CODE['first_lock'], 'msg' => '']);
            }
            // 到期前15天才可以兑换
            $expire_time = Member::where('user_id', $user->id)->value('expire_time');
            $is_renew = strtotime($expire_time) < strtotime(Carbon::today()->addDays(15));
            if (!$is_renew) {
                return response()->json(['code' => self::ERROR_CODE['cant_renew'], 'msg' => '']);
            }
            $code = strtoupper(trim($code));
            // 留了个小坑 会员兑换码现在有17和18位两种
            if ((strlen($code) == 18 || strlen($code) == 17) && substr($code, 0, 2) == 'HY') {   // 会员兑换码
                DB::beginTransaction();
                try {
                    $card = MemberCardRecord::where('code', $code)
                        ->where('status', MemberCardRecord::STATUS['is_pay'])
                        ->lockForUpdate()->first();
                    if ($card) {
                        if ($card->user_id == 0) {
                            // 兑换码已过期
                            if (strtotime($card->period_end) < strtotime(Carbon::today()->startOfDay())) {
                                return response()->json(['code' => self::ERROR_CODE['overdue'], 'msg' => '']);
                            } else if (strtotime($card->period_start) > strtotime(Carbon::today()->startOfDay())) {
                                // 未生效
                                return response()->json(['code' => self::ERROR_CODE['not_start'], 'msg' => '']);
                            }
                            // 给用户使用兑换码
                            $data = $this->exchangeCardCDKEY($card, $user);
                            DB::commit();
                            return $this->response->json(['code' => 0, 'msg' => '兑换成功', 'data' => $data]);
                        } else {
                            DB::commit();
                            return response()->json(['code' => self::ERROR_CODE['used'], 'msg' => '']);
                        }
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('EXCHANGE_CDKEY_ERROR', [$e]);
                    return response()->json(['code' => self::ERROR_CODE['sys_err'], 'msg' => '']);
                }
            }
        }
        $error_time++;
        IQuery::redisSet('CDKEY_ERROR_TIMES_' . $user->id, $error_time, 3600);
        return response()->json(['code' => self::ERROR_CODE['ineffective'], 'msg' => '']);
    }


    /**
     * 兑换会员兑换码
     */
    public function exchangeCardCDKEY(MemberCardRecord $card, $user)
    {
        $user_id = $user->id;
        $card_type = $card->card_type;
        $member = Member::where('user_id', $user_id)->first();
        $expire_time = Carbon::createFromTimestamp(strtotime($member->expire_time));
        // 续费则为时间累加
        $period_start = Carbon::now();
        if (strtotime($expire_time) >= strtotime(Carbon::today())) {
            $period_start = Carbon::createFromTimestamp(strtotime($expire_time))->addDay();
            $period_end = MemberCardRecord::getPeriodEnd($card_type, clone($period_start))->endOfDay();
        } else {
            $period_end = MemberCardRecord::getPeriodEnd($card_type, Carbon::today())->endOfDay();
        }
        $star_time = $member->star_time ?? Carbon::now();
        $member_type = $member->member_type > 0 ? $member->member_type : 1;
        $expire_time = $period_end;
        $star_level_id = StarLevel::where('exp_min', '<=', $member->star_exp)
            ->where('exp_max', '>=', $member->star_exp)
            ->value('id');
        DB::table('members')->where('user_id', $member->user_id)
            ->update([
                'star_level_id' => $star_level_id,
                'star_time' => $star_time
            ]);
        // 购卡福利
        createCardCoupon($card_type, $user_id);
        // 星球会员日赠饮券
        sendPrimeDayCoupon($user_id);
        // 星球会员纪念日券
        sendAnniversaryCoupon($user_id);
        // 发放每月福利
        createMonthGift($card_type, $user);
        DB::table('members')->where('user_id', $member->user_id)
            ->update(['expire_time' => $expire_time, 'member_type' => $member_type]);
        // 更改兑换码状态
        $card->paid_at = Carbon::now();
        $card->user_id = $user_id;
        $card->period_start = $period_start;
        $card->period_end = $period_end;
        $card->save();
        // 修改原因： 紧急需求 购买体验卡不发生日券 临时处理办法
        // 生日券  生日在本月 且不存在生日赠饮券  就给用户发一张生日赠饮券 可使用日期为会员生日当天
        sendBirthday($user_id);
        return MemberCardRecord::getCardCoupon($card_type);
    }

    /**
     * 获取用户使用兑换码的状态 true为锁住状态
     */
    public function exchangeStatus()
    {
        $user = $this->user();
        $lock = IQuery::redisGet('CDKEY_EXCHANGE_LOCK_' . $user->id);
        // 当前能不能续费
        // 先判断用户是不是星球会员有效期内 过期时间是否在15天内
        $expire_time = Member::where('user_id', $user->id)->value('expire_time');
        $is_renew = strtotime($expire_time) < strtotime(Carbon::today()->addDays(15));
        return response()->json([
            'lock' => $lock ? true : false,
            'status' => $is_renew
        ]);
    }

}
