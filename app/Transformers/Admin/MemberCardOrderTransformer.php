<?php

namespace App\Transformers\Admin;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Models\MemberCardRecord;

class MemberCardOrderTransformer extends TransformerAbstract
{
    const CARDTYPE = [
        '', '体验卡(15天)', '月卡(30天)', '季卡(3个月)', '半年卡(6个月)', '年卡(12个月)', 'Vka'
    ];
    
    /**
     * 
     * 会员卡数据获取和转化
     * @return array
     */
    public function transform(MemberCardRecord $member_card_record)
    {
        return [
            'id' => $member_card_record->id,
            'user_name' => $member_card_record->user->name,
            'user_phone' => $member_card_record->user->phone,
            'price' => $member_card_record->price,
            'quanty' => 1,
            'discount_fee' => '无',
            'fee_discount' => 0,
            'total_fee' => $member_card_record->price,
            'card_type' => self::CARDTYPE[$member_card_record->card_type],
            'paid_type' => $member_card_record->paid_type,
            'trade_type' => $member_card_record->trade_type,
            'status_code' => 1,
            'status_text' => '支付成功'
        ];
    }
}
