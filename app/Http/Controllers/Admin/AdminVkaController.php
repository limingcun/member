<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Repositories\Admin\MemberRepository;
use App\Models\StarLevel;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\VkaServer;
use App\Models\Member;
use DB;
use Carbon\Carbon;
use IQuery;
use App\Models\VkaRecord;
use App\Models\Product;
use App\Models\MemberCardRecord;
use App\Http\Controllers\Api\VkaController;

/*
 * 后端控制器
 */
class AdminVkaController extends ApiController
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
            'userId' => 'required|integer'
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
        $user = User::find($request->userId);
        $vka = new VkaController();
        if ($result['code'] == 0) {
            IQuery::redisSet($this->redis_path.$cardNo, $pwd, 60);
            DB::beginTransaction();
            try {
                //vka升级补入积分
                $score = $result['data']['property']['score'];
                $vka->vkaScore($user, $score);
                //等级经验值计入
                $exp = $result['data']['property']['exp'];
                //vka升级获取星球权益(之前有绑定vka记录，不能再次获取权益)
                if ($user->vkaRecord->isEmpty()) {
                    $vka->vkaGetLegal($user, $exp);
                    $first = Member::where('user_id', $user->id)->where('expire_time', '>=', Carbon::today())->select('id')->exists();
                    $gift_count = $first ? 36 : 35;
                    createMonthGift(MemberCardRecord::CARD_TYPE['vka'], $user, $gift_count);  //会员发福利
                    $vka->vkaExp($user, $exp, $score, true);
                    // 首次迁移且迁移前不是会员 单独发本月的福利券
                    if (!$first) {
                        $member = Member::where('user_id', $user->id)->select(['user_id', 'star_exp'])->first();
                        $star_level = StarLevel::where('exp_min', '<=', $member->star_exp)
                            ->where('exp_max', '>=', $member->star_exp)->select(['id', 'name'])->first();
                        $star_level_id = $star_level->id ?? 1;
                        starMonthlyWelfare($star_level_id, $member->user_id, MemberCardRecord::CARD_TYPE['vka']);
                    }
                } else {
                    $vka->vkaExp($user, $exp, $score);
                }
                //记录微卡兑换记录
                $vka->vkaRecordWrite($user, $cardNo);
                $vka->giftCardRecord($user, $cardNo);
                // 设置生日，发放生日券
                if ($request->birthday) {
                    $user->update(['birthday' => $request->birthday]);
                    $rps = new MemberRepository();
                    $rps->updateBirthday($user->id, $user->birthday);
                } else {
                    sendBirthday($user->id);
                }
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
}
