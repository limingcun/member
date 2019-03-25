<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\GiftRecord;
use App\Models\StarLevel;
use App\Transformers\Api\GiftListTransformer;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class GiftController extends ApiController
{

    /**
     * 查看用户所有礼包
     */
    public function show(Request $request)
    {
        $gifts = $this->user()->giftRecord()->whereIn('gift_type', [GiftRecord::GIFT_TYPE['go_update_cash'], GiftRecord::GIFT_TYPE['go_update_buy_fee']])
            ->where('start_at', '<=', Carbon::now())->orderBy('level_id', 'desc')->paginate($request['page_size'] ?? 20);
        $diamond_exp = StarLevel::where('name', '钻石')->select('exp_min')->first();
        // 当前是星球会员 且等级大于等于钻石
        $member = $this->user()->members()->select(['id', 'member_cup', 'star_level_id'])->where('expire_time', '>=', Carbon::today())
            ->where('star_exp', '>=', $diamond_exp->exp_min)->first();
        $star_level_name = $member->starLevel->name ?? '';
        $enough = $member ? getEnough($star_level_name)['enough'] : 0;
        $amount = $member ? getEnough($star_level_name)['amount'] : 0;
        $member_cup = $member->member_cup ?? 0;
        // 查询到的礼包记录存在新的就更新状态
        $gifts->where('status', GiftRecord::STATUS['new'])->count() > 0 ? $this->readGiftRecord($gifts) : false;
        return $this->response->collection($gifts, new GiftListTransformer(),
            [   'is_show' => $member ? true : false,
                'enough' => $enough,
                'amount' => $amount,
                'num' => $member_cup,
                'star_name' =>  $star_level_name
            ]);
    }

    // 更新礼包状态
    public function readGiftRecord($gifts)
    {
        foreach($gifts as $gift) {
            $gift_ids[] = $gift->id;
        }
        GiftRecord::whereIn('id', $gift_ids)->where('status', GiftRecord::STATUS['new'])
            ->update(['status' => GiftRecord::STATUS['read']]);     // 礼包状态修改
    }

    /**
     * 领取礼包
     */
    public function getGift($gift_id = null)
    {
        $data = ['code' => 4001, 'msg' => '领取失败！请稍后重试！'];
        // 注意判断领取用户的id和礼包的id是否相同
        $gift = $this->user()->giftRecord()->where('id', $gift_id)->where('overdue_at', '>=', Carbon::now())
            ->whereNull('pick_at')->where('start_at', '<=', Carbon::now())->first();
        if (isset($gift)) {
            DB::beginTransaction();
            try {
                // 发券
                $this->sendGift($gift);
                // 修改礼包状态
                $gift->pick_at = Carbon::now();
                $gift->save();
                DB::commit();
                $data['code'] = 0;
                $data['msg'] = '领取成功！';
            } catch (\Exception $exception) {
                DB::rollBack();
            }
        }
        return $this->response->json($data);
    }


    /**
     * 根据gift_type找到不同的礼包
     */
    public function sendGift(GiftRecord $giftRecord)
    {
        $level = substr($giftRecord->level->name, 2);
        $user_id = $this->user()->id;
        switch ($giftRecord->gift_type) {
            case GiftRecord::GIFT_TYPE['go_update_cash']:
                $this->goUpdateCash($level, $user_id);
                break;
            case GiftRecord::GIFT_TYPE['go_update_buy_fee']:
                $this->goUpdateBuyFee($level, $user_id);
                break;
//            case GiftRecord::GIFT_TYPE['star_update']:
//                $this->starUpdate($giftRecord->startLevel->name, $user_id);
//                break;
//            case GiftRecord::GIFT_TYPE['star_monthly_welfare']:
//                // 用户在获得礼包时的等级  查询在每月福利礼包开始日期前是否有升级礼包记录，有的话按照最新升级礼包的星球等级算，否则按照最低等级算
//                $giftRecord = $this->user()->giftRecord()->select('star_level_id')->where('gift_type', GiftRecord::GIFT_TYPE['star_update'])
//                    ->where('start_at', '>=', $giftRecord->start_at)->orderBy('id', 'desc')->first();
//                $star_level = $giftRecord ? $giftRecord->startLevel->name : '白银';
//                $this->starMonthlyWelfare($star_level, $user_id);
//                break;
//            case GiftRecord::GIFT_TYPE['star_first_charge']:
//                $this->starFirstCharge($user_id);
//                break;
//            case GiftRecord::GIFT_TYPE['star_birthday']:
//                $this->starBirthday($user_id);
//                break;
//            case GiftRecord::GIFT_TYPE['star_prime_day']:
//                $this->starPrimeDay($user_id);
//                break;
        }
    }

    /**
     * go会员升级礼包现金满减券
     */
    public function goUpdateCash($level, $user_id)
    {
        switch ($level) {
            case $level < 5:
                createCoupon('cash_120-5', $user_id, 3);
                break;
            case $level < 10:
                createCoupon('cash_110-10', $user_id, 3);
                break;
            case $level < 15:
                createCoupon('cash_110-15', $user_id, 3);
                break;
            case $level < 20:
                createCoupon('cash_100-15', $user_id, 3);
                break;
            case $level < 25:
                createCoupon('cash_100-20', $user_id, 3);
                break;
            case $level < 31:
                createCoupon('cash_100-25', $user_id, 3);
                break;
            default:
                break;
        }
    }

    /**
     * go会员升级礼包满赠券
     */
    public function goUpdateBuyFee($level, $user_id)
    {
        switch ($level) {
            case $level < 5:
                createCoupon('buy_fee_6-1', $user_id, 2);
                break;
            case $level < 10:
                createCoupon('buy_fee_5-1', $user_id, 2);
                break;
            case $level < 15:
                createCoupon('buy_fee_4-1', $user_id, 3);
                break;
            case $level < 20:
                createCoupon('buy_fee_3-1', $user_id, 3);
                break;
            case $level < 25:
                createCoupon('buy_fee_3-1', $user_id, 3);
                break;
            case $level < 31:
                createCoupon('buy_fee_2-1', $user_id, 3);
                break;
            default:
                break;
        }
    }

    /**
     * 星球会员每月福利礼包
     */
    public function starMonthlyWelfare($star_level, $user_id)
    {
        switch ($star_level) {
            case '白银':
                createCoupon('discount_star_month', $user_id, 1);
                createCoupon('cash_150-5', $user_id, 2);
                break;
            case '黄金':
                createCoupon('discount_star_month', $user_id, 2);
                createCoupon('queue_star_month', $user_id, 1);
                createCoupon('cash_150-10', $user_id, 2);
                break;
            case '铂金':
                createCoupon('discount_star_month', $user_id, 3);
                createCoupon('queue_star_month', $user_id, 1);
                createCoupon('buy_fee_3-1', $user_id, 2);
                createCoupon('cash_150-15', $user_id, 3);
                break;
            case '钻石':
                createCoupon('discount_star_month', $user_id, 3);
                createCoupon('queue_star_month', $user_id, 2);
                createCoupon('buy_fee_2-1', $user_id, 2);
                createCoupon('cash_150-20', $user_id, 3);
                break;
            case '黑金':
                createCoupon('discount_star_month', $user_id, 5);
                createCoupon('queue_star_month', $user_id, 2);
                createCoupon('buy_fee_2-1', $user_id, 3);
                createCoupon('cash_150-25', $user_id, 3);
                break;
            case '黑钻':
                createCoupon('discount_star_month', $user_id, 6);
                createCoupon('queue_star_month', $user_id, 3);
                createCoupon('buy_fee_1-1', $user_id, 2);
                createCoupon('cash_150-30', $user_id, 3);
                break;
            default:
                break;
        }
    }

    /**
     * 星球会员首充礼包
     */
    public function starFirstCharge($user_id)
    {
        createCoupon('buy_fee_star_1-1', $user_id, 1);
        createCoupon('cash_star_first', $user_id, 2);
    }

    /**
     * 星球会员生日礼包
     */
    public function starBirthday($user_id)
    {
        createCoupon('fee_star_birthday', $user_id, 1);
    }

    /**
     * 星球会员会员日礼包
     */
    public function starPrimeDay($user_id)
    {
        createCoupon('fee_star_prime_day', $user_id, 1);
    }
}
