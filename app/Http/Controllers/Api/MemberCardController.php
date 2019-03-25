<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\WechatController;
use App\Models\CardCodeOrder;
use App\Models\MemberCardRecord;
use App\Models\PosMemberCode;
use App\Transformers\Api\MemberCardRecordTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Log;
use DB;
use Ramsey\Uuid\Uuid;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MemberCardController extends ApiController
{

    /**
     * 付费会员卡列表
     */
    public function MemberCardList()
    {
        $expire_time = $this->user()->members()->select(['id', 'expire_time'])->value('expire_time');
        $is_star = strtotime($expire_time) > strtotime(Carbon::today());
        // 会员过期前15天可以续费
        $is_renew = strtotime($expire_time) < strtotime(Carbon::today()->addDays(15));
        $experience_num = $this->user()->memberCardRecord()->select('id')
            ->where('status', MemberCardRecord::STATUS['is_pay'])
            ->where('card_type', MemberCardRecord::CARD_TYPE['experience'])->exists();
        // V卡迁移不算首充 购买体验卡不算首充   兑换码兑换也不算首充
        $is_first = $this->user()->memberCardRecord()->select('id')
            ->where('card_type', '>', MemberCardRecord::CARD_TYPE['experience'])
            ->where('card_type', '<', MemberCardRecord::CARD_TYPE['vka'])
            ->where('paid_type', '!=', 4)
            ->where('status', MemberCardRecord::STATUS['is_pay'])->exists();
        $season_num = $this->user()->memberCardRecord()
            ->where('card_type', MemberCardRecord::CARD_TYPE['season'])
            ->where('paid_type', '=', 1)
            ->where('status', MemberCardRecord::STATUS['is_pay'])->count('id');
        $experience_card = [[
            'id' => MemberCardRecord::CARD_TYPE['experience'],
            'name' => '体验卡',
            'price' => MemberCardRecord::PRICE['experience'],
            'coupon' => [
                ['name' => '限定饮品9折券', 'amount' => 1,],
                ['name' => '买一赠一', 'amount' => 1,],
                ['name' => '买二赠一', 'amount' => 1,],
            ],
            'times' => $experience_num ? 1 : 0,
        ]];
        $card = [
            [
                'id' => MemberCardRecord::CARD_TYPE['season'],
                'name' => '季卡',
                'price' => MemberCardRecord::PRICE['season'],
                'coupon' => [
                    ['name' => '限定饮品9折券', 'amount' => 2,],
                    ['name' => '买一赠一', 'amount' => 2,],
                    ['name' => '买二赠一', 'amount' => 3,],
                    ['name' => '优先券', 'amount' => 1],
                ],
                'times' => $season_num
            ],
            [
                'id' => MemberCardRecord::CARD_TYPE['half_year'],
                'name' => '半年卡',
                'price' => MemberCardRecord::PRICE['half_year'],
                'coupon' => [
                    ['name' => '限定饮品9折券', 'amount' => 3,],
                    ['name' => '买一赠一', 'amount' => 3,],
                    ['name' => '买二赠一', 'amount' => 4,],
                    ['name' => '优先券', 'amount' => 1],
                ]
            ],
            [
                'id' => MemberCardRecord::CARD_TYPE['annual'],
                'name' => '年卡',
                'price' => MemberCardRecord::PRICE['annual'],
                'coupon' => [
                    ['name' => '限定饮品9折券', 'amount' => 2,],
                    ['name' => '买一赠一', 'amount' => 4,],
                    ['name' => '买二赠一', 'amount' => 5,],
                    ['name' => '免运费券', 'amount' => 2],
                    ['name' => '优先券', 'amount' => 2],
                ]
            ],
        ];
        // 活动有效期内才能买体验卡
        if (inviteActivityStatus() == 1) {
            $card = array_merge($experience_card, $card);
        }
        $arr = [
            'is_star' => $is_star,
            'is_first' => $is_first ? false : true,
            'is_renew' => $is_renew,
            'card' => $card
        ];
        return response()->json(['code' => 0, 'data' => $arr]);
    }

    /**
     * 购买会员卡
     * 4001 无法购买会员卡
     */
    public function buyMemberCard(Request $request)
    {
        // 购买体验卡后 仍是首充状态
        // 判断用户是否绑定手机号
        $is_bind = $this->user()->where('id', $this->user()->id)->whereNotnull('phone')->select('id')->exists();
        if (!$is_bind) {
            // 未绑定手机不可购卡
            return error_return(4001, '未绑定手机号');
        }
        $card_type = $request['type'];

        // 体验卡只允许新的go会员在活动有效期内购买
        if ($card_type == MemberCardRecord::CARD_TYPE['experience'] && inviteActivityStatus() != 1) {
            return error_return(4004, '活动不在有效期内');
        }

        $expire_time = $this->user()->members()->select(['id', 'expire_time'])->value('expire_time');
        if (!$this->checkCardRecord($card_type, $expire_time)) {
            return error_return(4002, '暂时不能购买');
        }
        $tradeType = $request['trade_type'] ?? 'JSAPI';
        if ($tradeType != 'JSAPI') {
            return error_return(4003, '仅支持JSAPI支付');
        }
//        $fee = MemberCardRecord::getPrice($card_type) * 100;
        // todo 修改价格
        $fee = 0.01 * 100;
        $app = new WechatController();
        $out_trade_no = date("YmdHis") . sprintf("%03d", rand(1, 999999)); //会员卡购买订单号
        $attributes = [
            'body' => '购买星球会员' . MemberCardRecord::CARD_NAME[$card_type],
            'out_trade_no' => $out_trade_no,
            'total_fee' => (int)$fee,
            'trade_type' => $tradeType, // JSAPI，NATIVE，APP...
            'notify_url' => env('APP_URL') . '/notification/wechat/notify',
            //'detail'           => '',
            'attach' => 'attach',
            'openid' => $this->user()->wxlite_open_id
        ];
        $result = $app->payment($attributes);

        if ($result['return_code'] != 'SUCCESS') {
            abort(500, '微信统一下单失败, ' . $result['return_msg']);
        } elseif (isset($result['err_code']) && $result['err_code'] == 'ORDERPAID') {
            abort(500, '微信统一下单失败, ' . $result['err_code_des']);
        }

        // 续费则为时间累加
        if (strtotime($expire_time) >= strtotime(Carbon::today())) {
            $period_start = Carbon::createFromTimestamp(strtotime($expire_time))->addDay();
            $period_end = MemberCardRecord::getPeriodEnd($card_type, clone($period_start))->endOfDay();
        } else {
            $period_start = Carbon::today();
            $period_end = MemberCardRecord::getPeriodEnd($card_type, Carbon::today())->endOfDay();
        }

        // 活动有效期控制
        $inviter_id = 0;
        if (inviteActivityStatus() == 1) {
            $inviter_id = $request['inviter_id'] ?? 0;
        }
        // 限制自己邀请自己
        if ($inviter_id == $this->user()->id) {
            $inviter_id = 0;
        }
        $memberCardRecord = MemberCardRecord::create([
            'user_id' => $this->user()->id,
            'card_no' => 0,       // 付费会员卡不存在卡号 默认为0
            'card_type' => $card_type,
            'price' => MemberCardRecord::getPrice($card_type),
            'period_start' => $period_start,
            'period_end' => $period_end,
            'order_no' => $out_trade_no,
            'prepay_id' => $result['prepay_id'],
            'trade_type' => $attributes['trade_type'],
            'inviter_id' => $inviter_id
        ]);
        $data = $app->jssdk($result['prepay_id'], false);
        // 返回订单id，用于购卡成功后轮询订单支付状态
        $data['id'] = $memberCardRecord->id;
        Log::info('payment_result', [$result]);
        return response()->json(['code' => 0, 'data' => $data]);
    }

    /**
     * 检查是否可以购卡
     */
    public function checkCardRecord($type, $expire_time)
    {
        $status = false;
        // 会员过期前15天可以续费
        $is_renew = strtotime($expire_time) < strtotime(Carbon::today()->addDays(15));
        if ($is_renew) {
            $status = true;
            // 体验卡只能买一次 季卡只能买4次
            if ($type == MemberCardRecord::CARD_TYPE['experience']) {
                $status = $this->user()->memberCardRecord()->select('id')
                    ->where('card_type', MemberCardRecord::CARD_TYPE['experience'])
                    ->where('status', MemberCardRecord::STATUS['is_pay'])
                    ->exists() ? false : true;
            } elseif ($type == MemberCardRecord::CARD_TYPE['season']) {
                $status = $this->user()->memberCardRecord()
                    ->where('card_type', MemberCardRecord::CARD_TYPE['season'])
                    ->where('paid_type', 1)
                    ->where('status', MemberCardRecord::STATUS['is_pay'])
                    ->count('id') < 4 ? true : false;
            }
        }
        return $status;
    }

    /**
     * 购卡记录列表
     */
    public function MemberCardRecordList(Request $request)
    {
        $memberCardList = $this->user()->memberCardRecord()
            ->where('card_no', 0)
            ->where('status', MemberCardRecord::STATUS['is_pay'])
            ->orderBy('id', 'DESC')
            ->paginate($request['page_size'] ?? 10);
        return $this->response->collection($memberCardList, new MemberCardRecordTransformer());
    }

    /**
     * 轮询获得付费会员卡购买状态
     */
    public function getStatus($id)
    {
        $status = MemberCardRecord::where('id', $id)->where('user_id', $this->user->id)
            ->where('status', MemberCardRecord::STATUS['is_pay'])->select('id')->exists();
//        if ($status) {
//            return response()->json(['code' => 0, 'status' => true]);
//        }
//        $count = \IQuery::redisGet('card_notify_' . $this->user->id);
//        if ($count) {
//            // 次数累加
//            \IQuery::redisSet('card_notify_' . $this->user->id, $count + 1, 120);
//        } else {
//            \IQuery::redisSet('card_notify_' . $this->user->id, 1, 120);
//        }
        // 存一个redis用来记录轮询次 轮询超过3次还没有回调的话 就主动查询
//        if (\IQuery::redisGet('card_notify_' . $this->user->id) > 2) {
//            DB::beginTransaction();
//            try {
//                $app = new WechatController();
//                $member_card = MemberCardRecord::where('id', $id)->first();
//                if (isset($member_card->order_no)) {
//                    $result = $app->queryByOutTradeNumber($member_card->order_no);
//                    if ($result['trade_state'] == 'SUCCESS') {
//                        $pay = new PaymentController();
//                        $pay->notifySuccess($result['time_end'], $member_card);
//                        DB::commit();
//                        $status = true;
//                        \Log::info('query pay order success');
//                    }
//                }
//            } catch (\Exception $e) {
//                DB::rollBack();
//                \Log::error('ERROR query pay order error!');
//            }
//            \IQuery::redisDelete('card_notify_' . $this->user->id);
//        }
        return response()->json(['code' => 0, 'status' => $status ? true : false]);
    }

    /**
     * 二维码
     */
    public function cardCode()
    {
        $user = $this->user();
        $member = $user->members[0];
//        if (!$member->member_no) return error_return(403, '不是会员');
        if (!$member->member_no) {
            $member->member_no = \IQuery::memberNoCreate($member->id, 6);
            $member->save();
        }
        $code = Uuid::uuid1()->getHex();
        PosMemberCode::create([
            'code' => $code,
            'member_no' => $member->member_no,
            'expire_at' => Carbon::now()->addMinute(10),
            'is_pay'=>\request('is_pay',0),
        ]);
        $qrcode = QrCode::format('png')
            ->size(312)
            ->margin(0)
            ->generate('istore:' . $code);
        $img = 'data:image/png;base64,' . base64_encode($qrcode);
        if ('json' == \request('type')) {
            return response()->json([
                'code' => $code,
                'img' => $img,
            ]);
        } else {
            echo $img;
        }
    }

    /**
     * 使用会员兑换码兑换会员
     */
    public function cardCodeExchange(Request $request)
    {
//        $user = $this->user();
//        $code = strtoupper(trim($request->code));  //将小写字母全转为大写
//        DB::beginTransaction();
//        try {
//            $cardCode = CardCodeOrder::where('code', $code)->lockForUpdate()->first();
//        } catch (\Exception $e) {
//
//        }
    }
}
