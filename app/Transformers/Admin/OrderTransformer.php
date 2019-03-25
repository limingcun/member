<?php

namespace App\Transformers\Admin;

use App\Models\Order;
use League\Fractal\TransformerAbstract;

class OrderTransformer extends TransformerAbstract 
{
    /**
     * Transform a order.
     *
     * @param  Order $order
     *
     * @return array
     */
    public function transform(Order $order)
    {
        return [
            'id' => $order->id,
            'time' => $order->paid_at,
            'shop_name' => $order->shop->name,
            'no' => $order->no,
            'total_fee' => $order->total_fee,
            'payment' => $order->payment,
            'order_score' => $order->member_score[0]->order_score,
            'total_score' => $order->member_score[0]->total_score,
            'coupon_type_name' => !($order->library->isEmpty()) ? $order->library[0]->template->type->name : null
        ];
    }
}
