<?php

namespace App\Transformers\Api;

use App\Models\MemberCardRecord;
use League\Fractal\TransformerAbstract;

class MemberCardRecordTransformer extends TransformerAbstract
{
    const TYPE = [
        1 => '购买',
        3 => '赠送',
        4 => '兑换'
    ];

    public function transform(MemberCardRecord $memberCard)
    {
        return [
            'name' => self::TYPE[$memberCard->paid_type] . '星球会员' . MemberCardRecord::CARD_NAME[$memberCard->card_type],
            'price' => $memberCard->price,
            'no' => $memberCard->order_no,
            'prepay_no' => $memberCard->prepay_id,
            'created_at' => $memberCard->created_at->toDateTimeString(),
            'paid_at' => $memberCard->paid_at,
            'type' => $memberCard->paid_type // type == 3 为后台赠送的会员
        ];
    }
}
