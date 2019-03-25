<?php

namespace App\Http\Controllers\Api;

use App\Models\GiftRecord;
use App\Models\MemberExp;
use App\Models\StarLevel;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\VkaServer;
use App\Models\MemberScore;
use App\Models\Member;
use DB;
use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\CouponLibrary;
use IQuery;
use App\Models\VkaRecord;
use App\Models\Product;
use App\Models\MemberCardRecord;
use App\Http\Controllers\notification\PaymentController;
use App\Policies\CouponLibrary\CashCouponPolicy; //现金券
use App\Policies\CouponLibrary\DiscountCouponPolicy; //折扣券
use App\Policies\CouponLibrary\FeeCouponPolicy; //赠饮券
use App\Policies\CouponLibrary\BuyFeeCouponPolicy; //买N送M券
use App\Policies\CouponLibrary\QueueCouponPolicy; //免排队券

class VkaController extends Controller
{
    //vka测试环境
//    const VKAURL = 'https://api.vi-ni.com/api/istore/card/';
//    const GOURL = 'https://api.vi-ni.com/api/istore/go/';
    //vka正式环境
    const VKAURL = 'https://api.v-ka.com/api/istore/card/';
    const GOURL = 'https://api.v-ka.com/api/istore/go/';

    protected $redis_path = 'laravel:redis:vka:';



    /*
     * error vka数据迁移失败
     * fail 账号密码不正确
     * lock 会员卡账户已升级,账号锁定
     * check vka接口调用不稳定
     */
    const CODE = [
        'error' => 4000,
        'fail' => 4001,
        'lock' => 4002,
        'check' => 4003
    ];

    /*
     * 升级用户数据信息
     * 积分升级
     * 会员等级升级
     * 会员优惠券升级
     * cardNo会员卡号
     * password密码
     * code：
     * 0迁移数据成功
     * 4000迁移数据失败
     * 4001账号或密码不正确
     * 4002会员卡账户已升级,账号锁定
     * 4003 vka接口调用不稳定
     */

    public function upgrade(Request $request) {
        $this->validate($request, [
            'cardNo' => 'required',
            'password' => 'required',
            'tab_type' => 'required|min:1'
        ]);
        $cardNo = $request->cardNo;
        $pwd = $request->password;
        if (IQuery::redisGet($this->redis_path.$cardNo)) {
            return response()->json(['code' => 4001, 'msg' => '请1分钟后重新迁移,迁移完成前不能做任何操作']);
        }
        if (VkaRecord::where('card_no', $cardNo)->first()) {
            return response()->json(['code' => self::CODE['lock'], 'msg' => '会员卡账户已升级']);
        }
        $server = new VkaServer();
        $url = self::VKAURL.$cardNo;
        $result = $server->getUpgrade($url, $pwd);
        if (!$result) {
            return response()->json(['code' => self::CODE['check'], 'msg' => 'vka接口调用不稳定']);
        }
        if ($result['code'] == 0) {
            IQuery::redisSet($this->redis_path.$cardNo, $pwd, 60);
            DB::beginTransaction();
            try {
                $user = $this->user();
                //vka升级补入积分
                $score = $result['data']['property']['score'];
                $this->vkaScore($user, $score);
                //等级经验值计入
                $exp = $result['data']['property']['exp'];
                //保存vka生日
                if (!$user->birthday) {
                    $vka_birthday = $result['data']['basic']['birthday'];
                    $this->saveVkaBirthday($user, $vka_birthday);
                }
                //vka升级获取星球权益(之前有绑定vka记录，不能再次获取权益)
                if ($user->vkaRecord->isEmpty()) {
                    $this->vkaGetLegal($user, $exp);
                    $first = Member::where('user_id', $user->id)->where('expire_time', '>=', Carbon::today())->select('id')->exists();
                    $gift_count = $first ? 36 : 35;
                    createMonthGift(MemberCardRecord::CARD_TYPE['vka'], $user, $gift_count);  //会员发福利
                    $this->vkaExp($user, $exp, $score, true);
                    // 首次迁移且迁移前不是会员 单独发本月的福利券
                    if (!$first) {
                        $member = Member::where('user_id', $user->id)->select(['user_id', 'star_exp'])->first();
                        $star_level = StarLevel::where('exp_min', '<=', $member->star_exp)
                            ->where('exp_max', '>=', $member->star_exp)->select(['id', 'name'])->first();
                        $star_level_id = $star_level->id ?? 1;
                        starMonthlyWelfare($star_level_id, $member->user_id, MemberCardRecord::CARD_TYPE['vka']);
                    }
                } else {
                    $this->vkaExp($user, $exp, $score);
                }
                //记录微卡兑换记录
                $this->vkaRecordWrite($user, $cardNo);
                $this->giftCardRecord($user, $cardNo);
                sendBirthday($user->id);
                //vka核销
                $urlgo = self::GOURL.$cardNo;
                $resgo = $server->getUpgrade($urlgo, $pwd);
                if (!$resgo['code']) {
                    IQuery::redisDelete($this->redis_path.$cardNo);
                    DB::commit();
                    return success_return($cardNo);
                } else {
                    DB::rollBack();
                    return response()->json(['code' => self::CODE['error'], 'msg' => 'vka数据迁移失败']);
                }
            } catch(\Exception $e) {
                DB::rollBack();
                \Log::info('VKA_ERROR', [$e]);
                return response()->json(['code' => self::CODE['error'], 'msg' => 'vka数据迁移失败']);
            }
        } else if ($result['code'] == 1001){
            return response()->json(['code' => self::CODE['fail'], 'msg' => '账号或密码不正确']);
        } else if ($result['code'] == 1002) {
            return response()->json(['code' => self::CODE['lock'], 'msg' => '会员卡账户已升级']);
        } else {
            return response()->json(['code' => $result['code'], 'msg' => $result['message']]);
        }
    }

    /**
     * 保存vka会员生日
     */
    public function saveVkaBirthday($user, $vka_birthday) {
        if ($vka_birthday) {
            $user->birthday = $vka_birthday;
            $user->save();
        }
    }

    /*
     * 积分数据迁移
     * $score积分
     */
    public function vkaScore($user, $score) {
        $value = $score['value'] ?? 0;
        MemberScore::create([
            'user_id' => $user->id,
            'score_change' => $value,
            'method' => MemberScore::METHOD['vka'],
            'description' => '星球移民',
            'member_type' => 1
        ]);
    }

    /*
     * 会员经验值升级
     * $user用户
     * $exp经验值
     * $score积分
     */
    public function vkaExp($user, $exp, $score, $first=false) {
        $exp_value = $exp['value'] ?? 0;
        $score_value = $score['value'] ?? 0;
        $member = $user->members->first();
        $order_score = bcadd($member->order_score, $score_value);
        $usable_score = bcadd($member->usable_score, $score_value);
        $star_time = $member->star_time ?? Carbon::now();
        $member_type = 2;
        $expire_time = $member->expire_time;
        // 首次迁移才会增加星球会员时间
        if ($first) {
            // 首次迁移且迁移前买过卡 在原来的时间上累加3年
            if ($member->expire_time) {
                $expire_time = Carbon::createFromTimestamp(strtotime($member->expire_time))->addYears(3)->format('Y-m-d');
            } else {
                $expire_time = Carbon::now()->addYears(3)->format('Y-m-d');
            }
        }
        $star_exp = bcadd($member->star_exp, $exp_value);
        $star_level_id = StarLevel::where('exp_min', '<=', $star_exp)->where('exp_max', '>=', $star_exp)->value('id');
        DB::table('members')->where('user_id', $member->user_id)
            ->update(['expire_time' => $expire_time, 'star_exp' => $star_exp, 'star_level_id' => $star_level_id, 'order_score' => $order_score,
                'usable_score' => $usable_score,  'star_time' => $star_time, 'member_type' => $member_type]);
        MemberExp::createMemberExp($member, $member->user_id, '0', VkaRecord::class,
            MemberExp::METHOD['vka'], 0, $star_exp, 'vka迁移获得');
    }

    /*
     * vka升级获取星球权益
     * $user用户
     * $exp经验值
     * 星球会员(<=999)升级后加500
     * 白银会员(>=1000,<=4999)升级后加1000
     * 黑金会员(>=5000)升级后加1500
     */
    public function vkaGetLegal($user, $exp) {
        $exp_value = $exp['value'] ?? 0;
        $member = $user->members->first();
        $rule = [];
        if ($exp_value <= 999) {
            $addValue = 500;
            $count = 2;
            $this->giftCouponTicket($user, Coupon::FLAG['vka_buyfee'], $count);
        } else if ($exp_value >= 1000 && $exp_value <= 4999) {
            $addValue = 1000;
            $count = 3;
            $this->giftCouponTicket($user, Coupon::FLAG['vka_buyfee'], $count);
        } else {
            $addValue = 1500;
            $count = 3;
            $this->giftCouponTicket($user, Coupon::FLAG['vka_buyfee'], $count);
            //赠饮券(默认赠送波波茶,没有波波茶则默认选择第一个商品)
            $count = 1;
            $this->giftCouponTicket($user, Coupon::FLAG['vka_fee'], $count, 1);
        }
        $member->update([
            'order_score' => bcadd($member->order_score, $addValue),
            'usable_score' => bcadd($member->usable_score, $addValue)
        ]);
        $this->vkaGiftPoint($user, $addValue);
    }

    /*
     * vka迁移赠送数据记录
     * $user用户
     * $value升级赠送数据
     */
    public function vkaGiftPoint($user, $value) {
        MemberScore::create([
            'user_id' => $user->id,
            'score_change' => $value,
            'method' => MemberScore::METHOD['vka'],
            'description' => '移民福利',
            'member_type' => 1
        ]);
    }

    /**
     * vka计入买卡记录
     */
    public function giftCardRecord($user, $card_no) {
        MemberCardRecord::create([
            'user_id' => $user->id,
            'card_no' => $card_no,
            'price' => 0,
            'period_start' => Carbon::today(),
            'period_end' => Carbon::today()->addYears(3)->format('Y-m-d'),
            'status' => 1,
            'paid_type' => 1,
            'card_type' => MemberCardRecord::CARD_TYPE['vka']
        ]);
    }


    /*
     * 赠券
     * $user用户
     * $name券名称
     * $policy策略类
     * $rule策略规则
     * $count数量
     */
    public function giftCouponTicket($user, $flag, $count) {
        $coupon = Coupon::where('flag', $flag)->first();
        //新建模板个人库
        for($i = 0; $i< $count; $i++) {
            $libraryArr[] = [
                'name' => $coupon->name,
                'user_id' => $user->id,
                'order_id' => 0,
                'coupon_id' => $coupon->id,
                'policy' => $coupon->policy,
                'policy_rule' => json_encode($coupon->policy_rule),
                'source_id' => $coupon->id,
                'source_type' => Coupon::class,
                'period_start' => Carbon::today(),
                'period_end' => Carbon::today()->addMonth(1),
                'status' => CouponLibrary::STATUS['surplus'],
                'code_id' => $coupon->no.IQuery::strPad(date('ymd').rand(100, 999)),
                'tab' => CouponLibrary::NEWTAB['new'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }
        DB::table('coupon_librarys')->insert($libraryArr);
        $user->members->first()->update(['new_coupon_tab' => Member::NEWTAB['new']]);
    }

    /*
     * vka升级记录录入
     * $user用户
     * $no会员卡号
     */
    public function vkaRecordWrite($user, $no) {
        VkaRecord::create([
            'user_id' => $user->id,
            'card_no' => $no,
            'status' => 1
        ]);
    }

    /*
     * vka账号流水记录
     */
    public function vkaRecord() {
        $user = $this->user();
        $vka_record = VkaRecord::where('user_id', $user->id)->orderBy('id', 'desc')->get();
        return response()->json($vka_record);
    }
}
