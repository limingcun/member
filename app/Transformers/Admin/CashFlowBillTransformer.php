<?php

namespace App\Transformers\Admin;

use App\Models\CashFlowBill;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class CashFlowBillTransformer extends TransformerAbstract
{
    /**
     * 
     * 储值账单数据获取和转化
     * @return array
     */
    
    public function transform(CashFlowBill $cash_flow_bill)
    {
        return [
            'id' => $cash_flow_bill->id,
            'time' => (string) $cash_flow_bill->created_at,
            'cash_type' => CashFlowBill::CASHTYPE[$cash_flow_bill->cash_type],
            'cash_money' => $this->payMethod($cash_flow_bill->cash_type).$cash_flow_bill->cash_money,
            'pay_way' => CashFlowBill::PAYWAY[$cash_flow_bill->pay_way],
            'trade_way' => CashFlowBill::TRADEWAY[$cash_flow_bill->trade_way],
            'free_money' => $cash_flow_bill->free_money,
            'store_name' => $cash_flow_bill->shop->name ?? '',
            'bill_no' => $cash_flow_bill->bill_no,
            'status' => !$cash_flow_bill->status ? '成功' : '失败'
        ];
    }
    
    /**
     * 消费方式
     */
    public function payMethod($cash_type) {
        if ($cash_type) {
            return '+';
        } else {
            return '-';
        }
    }
}
