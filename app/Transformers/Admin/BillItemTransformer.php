<?php

namespace App\Transformers\Admin;

use App\Models\CashFlowBill;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class BillItemTransformer extends TransformerAbstract 
{
    /**
     * Transform a order.
     *
     * @param  Order $order
     *
     * @return array
     */
    public function transform(CashFlowBill $cash_flow_bill)
    {
        return [
            'id' => $cash_flow_bill->id,
            'user_name' => $cash_flow_bill->user->name ?? '',
            'user_phone' => $cash_flow_bill->user->phone ?? '',
//            'member_type' => $this->memberType($cash_flow_bill->member ?? ''),
            'member_type' => CashFlowBill::MEMBERTYPE[$cash_flow_bill->member_type],
            'bill_no' => $cash_flow_bill->bill_no,
            'time' => (string) $cash_flow_bill->created_at,
            'trade_way' => CashFlowBill::TRADEWAY[$cash_flow_bill->trade_way],
            'cash_money' => $cash_flow_bill->cash_money,
            'payment' => $cash_flow_bill->payment
        ];
    }
    
    /**
     * 判断是星球会员还是Go会员
     * @param type $member
     */
    public function memberType($member) {
        if ($member == '') {
            return 'Go会员';
        }
        if ($member->expire_time) {
            if (Carbon::parse($member->expire_time)->timestamp >= Carbon::today()->timestamp) {
                    return '星球会员';
            }
        }
        return 'Go会员';
    }
}
