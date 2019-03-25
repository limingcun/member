<?php

namespace App\Transformers\Admin;

use App\Models\CardCodeOrder;
use App\Models\MemberCardRecord;
use League\Fractal\TransformerAbstract;
use DB;

class CDKEYTransformer extends TransformerAbstract
{

    public function transform($code)
    {
        return [
            'no' => $code->no,
            'code' => substr($code->code, 0,8).'***'.substr($code->code, -2),
            'use_name' => $code->u_name,
            'phone' => $code->phone,
            'pick_at' => $code->paid_at,
            'status' => $code->paid_at ? '已兑换' : '未兑换',
            'period_start' => $code->period_start . ' 00:00:00',
            'period_end' => $code->period_end . ' 23:59:59',
            'card_type' => MemberCardRecord::CARD_NAME[$code->card_type],
            'name' => $code->name,
            'order_id' => $code->id
        ];
    }
}
