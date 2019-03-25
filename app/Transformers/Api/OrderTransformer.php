<?php

namespace App\Transformers\Api;

use App\Models\Order;
use League\Fractal\TransformerAbstract;

class OrderTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['items', 'shop', 'address', 'delivery', 'user'];

    public function transform(Order $order)
    {
        return [
            'id' => (int)$order->id,
            'no' => $order->no,
            'outer_id' => $order->outer_id,
            'pickup_no' => $order->pickup_no,
            'pickup_code_qrcode' => route('orders.pickup.qrcode', $order->no),
            'pickup_type' => $order->is_takeaway ? '外卖' : '自提',
            //'user_id' => (int)$order->user_id,
            //'member_id' => (int)$order->member_id,
            //'shop_id' => $order->shop_id,
            'total_fee' => number_format($order->total_fee, 2),
            'box_fee' => number_format($order->box_fee, 2),
            'delivery_fee' => number_format($order->delivery_fee, 2),
            //'discount_fee' => number_format($order->discount_fee, 2),
            'payment' => number_format($order->payment, 2),
            //'paid_at' => $order->paid_at ? (string)$order->paid_at : null,
            //'printed_at' => $order->printed_at? (string)$order->printed_at: null,
            //'dispatched_at' => $order->dispatched_at ? (string)$order->dispatched_at: null,
            //'closed_at' => $order->closed_at? (string)$order->closed_at: null,
            //'paid_type' => $order->paid_type,
            'status' => $order->status ?: 'WAIT_BUYER_PAY',
            'refund_status' => $order->refund_status,
            //'transaction_no' => $order->transaction_no ?: '',
            'pickup_time' => $order->pickup_time ? $order->pickup_time->toDateTimeString() : null,
            'pickup_time_period' => $order->pickup_time_period,
            'is_takeaway' => $order->is_takeaway,
            'created_at' => (string)$order->created_at,
            //'updated_at' => (string)$order->updated_at,
        ];
    }

    public function includeItems(Order $order)
    {
        return $this->collection($order->items, new OrderItemTransformer());
    }

    public function includeShop(Order $order)
    {
        return $this->item($order->shop, new ShopTransformer());
    }

    public function includeAddress(Order $order)
    {
        if (!$order->is_takeaway || !$order->address) {
            return $this->null();
        }

        return $this->item($order->address, new OrderAddressTransformer());
    }

    public function includeDelivery(Order $order)
    {
        if (!$order->is_takeaway || !$order->delivery) {
            return $this->null();
        }

        return $this->item($order->delivery, new OrderDeliveryTransformer());
    }

    public function includeUser(Order $order)
    {
        return $this->item($order->user, new UserTransformer());
    }
}
