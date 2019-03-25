<?php

namespace App\Transformers\Admin;

use App\Models\CardCodeOrder;
use App\Models\MemberCardRecord;
use League\Fractal\TransformerAbstract;
use DB;

class CardOrderTransformer extends TransformerAbstract
{

    public function transform(CardCodeOrder $cardOrder)
    {
        return [
            'id' => $cardOrder->id,
            'name' => $cardOrder->name,
            'phone' => $cardOrder->phone,
            'email' => $cardOrder->email,
            'created_at' => $cardOrder->created_at->toDateTimeString(),
            'card_type' => MemberCardRecord::CARD_NAME[$cardOrder->card_type],
            'price' => $cardOrder->price,
            'count' => $cardOrder->count,
            'status' => CardCodeOrder::STATUS['unused'] == $cardOrder->status ? '未导出' : '已导出',
            'address' => $cardOrder->address,
            'period_start' => $cardOrder->period_start,
            'period_end' => $cardOrder->period_end,
            'admin_id' => $cardOrder->admin_id,
            'admin_name' => DB::table('istore_upms_user_t')->where('id', $cardOrder->admin_id)->value('name')
        ];
    }
}
