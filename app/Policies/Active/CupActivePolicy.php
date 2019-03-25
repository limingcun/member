<?php
/**
 * 满多少杯活动
 * （下单后触发）
 */

namespace App\Policies\Active;


use App\Models\Active;
use App\Models\Order;
use App\Policies\Policy;

class CupActivePolicy extends Policy
{
    protected $rules = [
        'cup' => 'required|integer', //满多少杯
    ];

    /**
     * 验证订单是否可以参与活动
     * @param Order $order
     * @param Active $active
     * @return bool
     */
    public function order(Order $order, Active $active)
    {
        return $order->item->sum('quantity') > $active->policy_rule['cup'] ? true : false;
    }
}