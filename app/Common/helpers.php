<?php
/**
 * Created by PhpStorm.
 * User: surui
 * Date: 2017/11/16
 * Time: 下午1:02
 */

use App\Http\Controllers\ErrorCode;
use App\Models\{Coupon, CouponLibrary, MemberScore, GiftRecord, User, MemberCardRecord};
use Carbon\Carbon;

if (!function_exists('error_return')) {
    function error_return($errorCode, $result = null)
    {
        if ($result == null) {
            $result = ['msg' => ''];
        }
        return [
            'code' => $errorCode,
            'msg' => ErrorCode::errorMsg($errorCode),
            'result' => $result
        ];
    }
}


if (!function_exists('success_return')) {
    function success_return($result = null)
    {
        if ($result == null) {
            return ['code' => 0, 'result' => ['msg' => '']];
        } else {
            return ['code' => 0, 'result' => $result];
        }
    }
}

/**
 * 取券模板发券
 */
function createCoupon($tpl, $user_id, $amount, $period_end = null, $period_start = null)
{
    if ($tpl == 'fee_star_birthday') {
        // 只购买体验卡的不发生日券
        $exists = MemberCardRecord::where('user_id', $user_id)
            ->where('status', MemberCardRecord::STATUS['is_pay'])
            ->where('card_type', '!=', MemberCardRecord::CARD_TYPE['experience'])
            ->exists();
        if (!$exists) {
            return false;
        }
    }
    // 取最新的券模板
    $coupon = Coupon::where('flag', Coupon::FLAG[$tpl])
        ->orderBy('updated_at', 'desc')->first();
    // 发券的时候设置通知提醒
    // 存一个20秒过期的redis记录 首次发券后创建消息 再次建券时 有记录就不创建消息了
    if (!IQuery::redisGet('msg_remind_' . $user_id)) {
        IQuery::redisSet('msg_remind_' . $user_id, 1, 20);
        \App\Models\Message::couponsGetMsg($user_id);
        DB::table('members')
            ->where('user_id', $user_id)
            ->increment('message_tab', 1, ['new_coupon_tab' => 1]);
    }
    return createCouponLibrarys($user_id, $coupon, $amount, $period_end, $period_start);
}

/**
 * 创建个人优惠券
 */
function createCouponLibrarys($user_id, Coupon $coupon, $amount, $period_end = null, $period_start = null)
{
    for ($i = 0; $i < $amount; $i++) {
        $library = CouponLibrary::create([
            'name' => $coupon->name,
            'user_id' => $user_id,
            'order_id' => 0,
            'coupon_id' => $coupon->id,
            'policy' => $coupon->policy,
            'policy_rule' => $coupon->policy_rule,
            'source_id' => $coupon->id,
            'source_type' => Coupon::class,
            'period_start' => $period_start ? $period_start : \Carbon\Carbon::today(),
            'period_end' => $period_end ? $period_end : Coupon::getTimePeriod($coupon), // 默认以优惠券模板为准
            'status' => CouponLibrary::STATUS['surplus'],
            'tab' => CouponLibrary::NEWTAB['new'],
            'code_id' => $coupon->no . @IQuery::strPad($i + 1),
            'use_limit' => $coupon->use_limit
        ]);
    }
    return $library;
}

/**
 * 给用户发放积分
 */
function createPoint($user_id, $amount, $method, $description, $order_id=0)
{
    try {
        $member_score = MemberScore::create([
            'user_id' => $user_id,
            'source_id' => $order_id,
            'source_type' => MemberCardRecord::class,
            'score_change' => $amount,
            'method' => MemberScore::METHOD[$method],
            'description' => $description,
            'member_type' => 1
        ]);
        if ($member_score) {
            DB::table('members')->where('user_id', $user_id)->increment('order_score', $amount, ['usable_score' => DB::raw("`usable_score` + $amount")]);
        }
    } catch (Exception $exception) {
        Log::error("用户 {$user_id} 积分 {$amount} 发放失败", [$exception]);
    }
}

/**
 * 发放礼包
 */
function createGiftRecord($gift)
{
    $gift_record = GiftRecord::create([
        'user_id' => $gift['user_id'],
        'name' => $gift['name'],
        'gift_type' => $gift['gift_type'],
        'level_id' => $gift['level_id'],
        'star_level_id' => $gift['star_level_id'],
        'overdue_at' => $gift['overdue_at'],
        'start_at' => $gift['start_at'],
    ]);
    return $gift_record;
}

if (!function_exists('system_varable')) {
    function system_varable($key, $value = 'value')
    {
        return DB::table('system_variables')
            ->where('key', $key)
            ->value($value);
    }
}

// 满xx单兑换赠饮券
function getEnough($star_level)
{
    switch ($star_level) {
        case '钻石':
            return ['enough' => 20, 'amount' => 1];
        case '黑金':
            return ['enough' => 10, 'amount' => 2];
        case '黑钻':
            return ['enough' => 5, 'amount' => 3];
        default:
            return ['enough' => 0, 'amount' => 0];
    }
}

function createMonthGift($card_type, User $user, $gift_nums=0)
{
    // 发放会员剩余月份的福利券 如季卡 买卡时发放当月福利 剩余两月的福利
    switch ($card_type) {
        case MemberCardRecord::CARD_TYPE['season']:
            $gift_nums = 2;
            break;
        case MemberCardRecord::CARD_TYPE['half_year']:
            $gift_nums = 5;
            break;
        case MemberCardRecord::CARD_TYPE['annual']:
            $gift_nums = 11;
            break;
//        case MemberCardRecord::CARD_TYPE['vka']:
//            $gift_nums = 35;
//            break;
    }
    $gift_type = GiftRecord::GIFT_TYPE['star_monthly_welfare'];
    $user_id = $user->id;
    sendWelfare($user_id, $card_type, $gift_type, $gift_nums);
}

/**
 * 发放每月福利
 */
function sendWelfare($user_id, $card_type, $gift_type, $gift_nums)
{
    $member = \App\Models\Member::where('user_id', $user_id)->select(['level_id', 'star_level_id', 'star_exp', 'expire_time'])->first();
    $level_id = $member->level_id;
    $star_level_id = $member->star_level_id;
    $start = strtotime(Carbon::now());
    // vka迁移
    if ($card_type == MemberCardRecord::CARD_TYPE['vka']) {
        // 迁移前就开通了会员
        if (isset($member->expire_time) && strtotime($member->expire_time) >= strtotime(Carbon::today())) {
            $gift = GiftRecord::where('user_id', $user_id)->where('gift_type', $gift_type)->orderBy('overdue_at', 'DESC')->first();
            if (isset($gift->start_at)) {
                $start = strtotime($gift->start_at);
            }
        } else {
            // 首次迁移 且 目前不是会员
//            starMonthlyWelfare($star_level_id, $user_id, $gift_type);
            // 发放生日券 会员日 纪念日券
            sendBirthday($user_id);
            sendPrimeDayCoupon($user_id);
            sendAnniversaryCoupon($user_id);
        }
    } else {
        // 续费
        if (isset($member->expire_time) && strtotime($member->expire_time) >= strtotime(Carbon::today())) {
            $gift = GiftRecord::where('user_id', $user_id)->where('gift_type', $gift_type)->orderBy('overdue_at', 'DESC')->first();
            if (isset($gift->start_at)) {
                $start = strtotime($gift->start_at);
            } else {
                // 只有购买体验卡后续费才会到这里
                $experience = MemberCardRecord::where('status', MemberCardRecord::STATUS['is_pay'])
                    ->where('card_type', MemberCardRecord::CARD_TYPE['experience'])
                    ->where('user_id', $user_id)
                    ->first();
                if ($experience) {
                    // 减掉一个月是因为买体验卡的时候，不存在每月礼包 这里做处理是去取体验卡的结束时间，以这个时间+1天为起点存每月福利礼包
                    $start = strtotime(Carbon::createFromTimestamp(strtotime($experience->period_end))
                        ->addDay()->subMonthNoOverflow());
                }
            }
            $gift_nums = $gift_nums + 1;
        } else {
            // 发放购卡当月福利
            // 由于经验值更新后，等级并没有马上更新，所以此处根据经验值再去查一次现在的等级id
            $star_level = \App\Models\StarLevel::where('exp_min', '<=', $member->star_exp)
                ->where('exp_max', '>=', $member->star_exp)->select(['id', 'name'])->first();
            $star_level_id = $star_level->id ?? 1;
            starMonthlyWelfare($star_level_id, $user_id, $card_type);
        }
    }
    // 剩余月份的福利  存成礼包发放
    for ($i = $gift_nums; $i > 0; $i--) {
        $gift = [
            'user_id' => $user_id,
            'name' => '星球会员每月福利礼包',
            'gift_type' => $gift_type,
            'level_id' => $level_id,
            'star_level_id' => $star_level_id,
            'overdue_at' => Carbon::createFromTimestamp($start)->addMonthsNoOverflow($i + 1),
            'start_at' => Carbon::createFromTimestamp($start)->addMonthsNoOverflow($i),
        ];
        createGiftRecord($gift);
    }
}


/**
 * 星球会员每月福利礼包
 */
function starMonthlyWelfare($star_level_id, $user_id, $card_type)
{
    $period_end = null;
    if ($card_type == MemberCardRecord::CARD_TYPE['experience']) {
        $period_end = \Carbon\Carbon::today()->addDays(15);
    }
    $star_level = \App\Models\StarLevel::where('id', $star_level_id)->select('name')->first();
    $star_level_name = $star_level->name ?? '白银';
    switch ($star_level_name) {
        case '白银':
            createCoupon('discount_star_month', $user_id, 1, $period_end);
            createCoupon('cash_150-5', $user_id, 2, $period_end);
            break;
        case '黄金':
            createCoupon('discount_star_month', $user_id, 2, $period_end);
            createCoupon('queue_star_month', $user_id, 1, $period_end);
            createCoupon('cash_150-10', $user_id, 2, $period_end);
            break;
        case '铂金':
            createCoupon('discount_star_month', $user_id, 3, $period_end);
            createCoupon('queue_star_month', $user_id, 1, $period_end);
            createCoupon('buy_fee_star_3-1', $user_id, 2, $period_end);
            createCoupon('cash_150-15', $user_id, 3, $period_end);
            break;
        case '钻石':
            createCoupon('discount_star_month', $user_id, 3, $period_end);
            createCoupon('queue_star_month', $user_id, 2, $period_end);
            createCoupon('buy_fee_star_2-1', $user_id, 2, $period_end);
            createCoupon('cash_150-20', $user_id, 3, $period_end);
            break;
        case '黑金':
            createCoupon('discount_star_month', $user_id, 5, $period_end);
            createCoupon('queue_star_month', $user_id, 2, $period_end);
            createCoupon('buy_fee_star_2-1', $user_id, 3, $period_end);
            createCoupon('cash_150-25', $user_id, 3, $period_end);
            break;
        case '黑钻':
            createCoupon('discount_star_month', $user_id, 6, $period_end);
            createCoupon('queue_star_month', $user_id, 3, $period_end);
            createCoupon('buy_fee_star_1-1', $user_id, 2, $period_end);
            createCoupon('cash_150-30', $user_id, 3, $period_end);
            // 黑钻有两张会员纪念日券
            blackDiamondAnniversaryCoupon($user_id);
            break;
        default:
            break;
    }
}


/**
 * 发生日券
 */
function sendBirthday($user_id)
{
    // 生日券  生日在本月 且不存在生日赠饮券  就给用户发一张生日赠饮券 可使用日期为会员生日当天
    // 这里直接用user对象查询的时候竟然没有user_id
    $is_birthday = User::where('id', $user_id)->select(['id', 'birthday'])->whereMonth('birthday', date('m'))
        ->whereDay('birthday', '>=', date('d'))->first();
    if (isset($is_birthday->birthday)) {
        $coupon = Coupon::where('flag', Coupon::FLAG['fee_star_birthday'])->select('id')->first();
        $is_send = CouponLibrary::where('user_id', $user_id)->select('id')->where('coupon_id', $coupon->id)
            ->where('period_start', '>=', Carbon::today())->exists();
        if (!$is_send) {
            $birth = strtotime($is_birthday->birthday);
            createCoupon('fee_star_birthday', $user_id, 1,
                Carbon::create(date('Y'), date('m', $birth), date('d', $birth))->endOfDay(),
                Carbon::create(date('Y'), date('m', $birth), date('d', $birth))->startOfDay());
        }
    }
}

/**
 * 发星球会员日赠饮券
 */
function sendPrimeDayCoupon($user_id)
{
    // 星球会员日赠饮券
    $coupon = Coupon::where('flag', Coupon::FLAG['fee_star_prime_day'])->select('id')->first();
    $is_prime = CouponLibrary::where('user_id', $user_id)->select('id')->where('coupon_id', $coupon->id)
        ->where('period_start', '>=', Carbon::today())->exists();
    if (!$is_prime) {
        if (Carbon::today()->lte(Carbon::create(date('Y'), 5, 12)->startOfDay())) {
            createCoupon('fee_star_prime_day', $user_id, 1,
                Carbon::create(date('Y'), 5, 12)->endOfDay(),
                Carbon::create(date('Y'), 5, 12)->startOfDay());
        } else {
            createCoupon('fee_star_prime_day', $user_id, 1,
                Carbon::create(date('Y'), 5, 12)->addYear(1)->endOfDay(),
                Carbon::create(date('Y'), 5, 12)->addYear(1)->startOfDay());
        }
    }
}

/**
 * 发星球会员纪念日赠饮券
 */
function sendAnniversaryCoupon($user_id)
{
    $coupon = Coupon::where('flag', Coupon::FLAG['fee_star_anniversary'])->select('id')->first();
    $is_anniversary = CouponLibrary::where('user_id', $user_id)->select('id')->where('coupon_id', $coupon->id)
        ->where('period_start', '>=', Carbon::today())->exists();
    if (!$is_anniversary) {
        // 不存在纪念日赠饮券
        // 判断是否设置了star_time
        $star_time = \App\Models\Member::where('user_id', $user_id)->value('star_time');
        if ($star_time) {
            $star_time = strtotime($star_time);
            if (strtotime(Carbon::create(date('Y'), date('m', $star_time), date('d', $star_time))->endOfDay()) <= strtotime(Carbon::today()->endOfDay())) {
                createCoupon('fee_star_anniversary', $user_id, 1,
                    Carbon::create(date('Y'), date('m', $star_time), date('d', $star_time))->addYear(1)->endOfDay(),
                    Carbon::create(date('Y'), date('m', $star_time), date('d', $star_time))->addYear(1)->startOfDay());
            } else {
                createCoupon('fee_star_anniversary', $user_id, 1,
                    Carbon::create(date('Y'), date('m', $star_time), date('d', $star_time))->endOfDay(),
                    Carbon::create(date('Y'), date('m', $star_time), date('d', $star_time))->startOfDay());
            }
        } else {
            // 没有设置
            createCoupon('fee_star_anniversary', $user_id, 1, Carbon::today()->addYear(1)->endOfDay(), Carbon::today()->addYear(1)->startOfDay());
        }
    }
}

/**
 * 购卡成功后发放会员卡奖励
 */
function createCardCoupon($type, $user_id)
{
    switch ($type) {
        case MemberCardRecord::CARD_TYPE['experience']:
            $period_end = Carbon::now()->addDays(15);
            createCoupon('discount_card', $user_id, 1, $period_end);
            createCoupon('buy_fee_card_1-1', $user_id, 1, $period_end);
            createCoupon('buy_fee_card_2-1', $user_id, 1, $period_end);
            break;
        case MemberCardRecord::CARD_TYPE['season']:
            $period_end = Carbon::now()->addMonthsNoOverflow(3);
            createCoupon('discount_card', $user_id, 2, $period_end);
            createCoupon('buy_fee_card_1-1', $user_id, 2, $period_end);
            createCoupon('buy_fee_card_2-1', $user_id, 3, $period_end);
            createCoupon('queue_card', $user_id, 1, $period_end);
            break;
        case MemberCardRecord::CARD_TYPE['half_year']:
            $period_end = Carbon::now()->addMonthsNoOverflow(6);
            createCoupon('discount_card', $user_id, 3, $period_end);
            createCoupon('buy_fee_card_1-1', $user_id, 3, $period_end);
            createCoupon('buy_fee_card_2-1', $user_id, 4, $period_end);
            createCoupon('queue_card', $user_id, 1, $period_end);
            break;
        case MemberCardRecord::CARD_TYPE['annual']:
            $period_end = Carbon::now()->addMonthsNoOverflow(12);
            createCoupon('discount_card', $user_id, 2, $period_end);
            createCoupon('buy_fee_card_1-1', $user_id, 4, $period_end);
            createCoupon('buy_fee_card_2-1', $user_id, 5, $period_end);
            createCoupon('fee_card_take_fee', $user_id, 2, $period_end);
            createCoupon('queue_card', $user_id, 2, $period_end);
            break;
    }
}

/**
 * 黑钻有两张会员纪念日券
 */
function blackDiamondAnniversaryCoupon($user_id)
{
    $coupon = Coupon::where('flag', Coupon::FLAG['fee_star_anniversary'])->select('id')->first();
    $count = CouponLibrary::where('user_id', $user_id)->where('coupon_id', $coupon->id)
        ->where('period_start', '>=', Carbon::today())->select('id')->count();
    if ($count < 2) {
        $num = 2 - $count;
        $star_time = \App\Models\Member::where('user_id', $user_id)->value('star_time');
        if ($star_time) {
            // 设置过
            $star_time = strtotime($star_time);
            if (strtotime(Carbon::create(date('Y'), date('m', $star_time),
                    date('d', $star_time))->endOfDay()) <= strtotime(Carbon::today()->endOfDay())) {
                createCoupon('fee_star_anniversary', $user_id, $num,
                    Carbon::create(date('Y'), date('m', $star_time), date('d', $star_time))->addYear(1)->endOfDay(),
                    Carbon::create(date('Y'), date('m', $star_time), date('d', $star_time))->addYear(1)->startOfDay());
            } else {
                Log::info(' 不加');
                createCoupon('fee_star_anniversary', $user_id, $num,
                    Carbon::create(date('Y'), date('m', $star_time), date('d', $star_time))->endOfDay(),
                    Carbon::create(date('Y'), date('m', $star_time), date('d', $star_time))->startOfDay());
            }
        }
    }
}


//星球会员升级
function starLevel(\App\Models\Member $member) {
    $star_exp = $member->star_exp;
    $star_level_id = $member->star_level_id;
    $star_level = \App\Models\StarLevel::where('exp_min', '<=', $star_exp)->where('exp_max', '>=', $star_exp)->select(['id', 'name'])->first();
//            if ($star_level_id != $star_level->id && $member->expire_time >= Carbon::today()->toDateString()) {
    if ($star_level_id != $star_level->id) {
        $member->where('id', $member->id)->update(['star_level_id' => $star_level->id]);
        if (($star_level_id < $star_level->id) && $star_level_id != 0) {    // $star_level_id != 0 没有这个判断 可能潜藏着一个bug 经验值增加从0 增加时可能会发一个升级礼包
            for ($i = $star_level_id + 1; $i <= $star_level->id; $i++) {
                $gift_type = GiftRecord::GIFT_TYPE['star_update'];
                // 首次升级
                $first_update = $member->user->giftRecord()->where('gift_type', $gift_type)
                    ->where('star_level_id', $i)->select('id')->exists();
                // 避免降级再升级时重复发放礼包
                if (!$first_update) {
                    try {
//                             星球会员升级瞬间礼包
                        // 礼包废除 改为直接发放 此处为记录星球会员升级记录
                        // 由于升级福利要延迟12小时之后再发 故在星球会员升级时先发放升级礼包 之后用定时器触发领取礼包
                        $gift = [
                            'user_id' => $member->user->id,
                            'name' => '星球会员升级礼包',
                            'gift_type' => $gift_type,
                            'level_id' => $member->level_id,
                            'star_level_id' => $i,
//                                    'pick_at' => Carbon::now(),
                            'overdue_at' => Carbon::now()->addHours(12)->addMonth(),
                            'start_at' => Carbon::now()->addHours(12),   // 礼包12小时后才能领取
                        ];
                        createGiftRecord($gift);
                        Log::info('MEMBER_STAR_LEVEL_UPDATE', ['member id=', $member->id, 'name', $member->name]);
                    } catch (\Exception $exception) {
                        Log::error("星球会员升级瞬间礼包发送失败 member_id = {$member->id}", [$exception]);
                    }
                }
            }
        } elseif ($star_level_id > $star_level->id) {
            // 降级时 存在还没有生效的升级礼包记录 就 删除礼包
            $gifts = GiftRecord::where('user_id', $member->user_id)->whereNull('pick_at')
                ->where('star_level_id', '>', $star_level->id)
                ->where('gift_type', GiftRecord::GIFT_TYPE['star_update'])
                ->select('id')->where('start_at', '>', Carbon::now())->get();
            foreach ($gifts as $gift) {
                $gift->delete();
            }
        }
    }
}

// Go会员升级
function goLevel(\App\Models\Member $member) {
    $exp = $member->exp;
    $level_id = $member->level_id;
    $level = \App\Models\Level::where('exp_min', '<=', $exp)->where('exp_max', '>=', $exp)->select(['id', 'name'])->first();
    if ($level_id != $level->id) {
        $member->where('id', $member->id)->update(['level_id' => $level->id]);
        $go_update_cash = GiftRecord::GIFT_TYPE['go_update_cash'];
        $go_update_buy_fee = GiftRecord::GIFT_TYPE['go_update_buy_fee'];
        if ($level_id < $level->id) {
            for ($i = $level_id + 1; $i <= $level->id; $i++) {
                // 用户降级之后 过期未领取  再次升级时 再发一次礼包 但是只要领了一种，本等级段就不再发了
                $first_update = $member->user->giftRecord()->whereIn('gift_type', [$go_update_cash, $go_update_buy_fee])
                    ->where('level_id', $i)->where(function ($query) {
                        // 已领取或未过期
                        $query->whereNotNull('pick_at')->orWhere('overdue_at', '>', Carbon::now());
                    })->select('id')->exists();
                // 避免降级再升级时重复发放礼包
                if (!$first_update) {
                    try {
                        $gift = [
                            'user_id' => $member->user->id,
                            'name' => '升级奖励',
                            'gift_type' => $go_update_cash,
                            'level_id' => $i,
                            'star_level_id' => $member->star_level_id,
                            'overdue_at' => Carbon::now()->addHours(12)->addDays(7),
                            'start_at' => Carbon::now()->addHours(12),   // 礼包12小时后才能领取
                        ];
                        createGiftRecord($gift);
                        $gift = [
                            'user_id' => $member->user->id,
                            'name' => '升级奖励',
                            'gift_type' => $go_update_buy_fee,
                            'level_id' => $i,
                            'star_level_id' => $member->star_level_id,
                            'overdue_at' => Carbon::now()->addHours(12)->addDays(7),
                            'start_at' => Carbon::now()->addHours(12),   // 礼包12小时后才能领取
                        ];
                        createGiftRecord($gift);
                        // 小程序首页小红点引导提示 如果存在就不设置了，因为会覆盖掉反馈中心的提示
                        if (!\IQuery::redisGet('hint_'.$member->user_id)) {
                            \IQuery::redisSet('hint_'.$member->user_id, Carbon::now()->addHours(12)->toDateTimeString());
                        }
                        Log::info('MEMBER_LEVEL_UPDATE', ['member id=', $member->id, 'name', $member->name]);
                    } catch (\Exception $exception) {
                        Log::error("go会员升级礼包发放失败 member_id = {$member->id}", [$exception]);
                    }
                }
            }
        } elseif ($level_id > $level->id) {
            // 降级时 存在还没有生效的升级礼包记录 就 删除礼包
            $gifts = GiftRecord::where('user_id', $member->user_id)->whereNull('pick_at')
                ->where('level_id', '>', $level->id)
                ->whereIn('gift_type', [$go_update_cash, $go_update_buy_fee])
                ->select('id')->where('start_at', '>', Carbon::now())->get();
            foreach ($gifts as $gift) {
                $gift->delete();
            }
        }
    }
}

/**
 * 九块九裂变活动状态
 */
function inviteActivityStatus()
{
    $status = system_varable('inviter_activity_switch', $value = 'value');
    if ($status == 1) {
        return 1;
    } else {
        return 0;
    }
}
