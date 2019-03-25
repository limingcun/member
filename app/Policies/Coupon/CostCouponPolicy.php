<?php

namespace App\Policies\Coupon;

use App\Models\Coupon;
use App\Models\CouponLibrary;
use App\Models\Order;
use App\Policies\CouponLibrary\CashCouponPolicy;
use App\Policies\Policy;
use Carbon\Carbon;
use function dd;
use Exception;


/**
 * 根据订单的消费额度生成优惠券
 */
class CostCouponPolicy extends Policy
{

    /**
     * 发送优惠券
     * @param Order $order
     * @param Coupon $coupon
     * @throws Exception
     */
    public function sendCoupon(Order $order, Coupon $coupon)
    {
        //计算使用时间
        if (1 == $coupon->period_type) {
            $period_start = $coupon->period_start;
            $period_end = $coupon->period_end;
        } elseif (2 == $coupon->period_type) {
            $period_start = Carbon::now();
            $period_end = Carbon::now()->addDay($coupon->period_day);
        } else {
            throw new Exception('周期类型错误');
        }
        //发放优惠券
        #todo 发券数量限制
        foreach ($order->item as $orderItem) {
            for ($i = 0; $i < $orderItem->quantity; $i++) {
                CouponLibrary::create([
                    'name' => $coupon->name,
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'coupon_id' => $coupon->id,
                    'policy' => 'CashCouponPolicy',
                    'policy_rule' => [
                        'enough' => 0,
                        'cut' => $orderItem->price
                    ],
                    'source_id' => $order->id,
                    'source_type' => Order::class,
                    'period_start' => $period_start,
                    'period_end' => $period_end,
                ]);
            }
        }
    }
}