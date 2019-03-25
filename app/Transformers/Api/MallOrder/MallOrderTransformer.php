<?php

namespace App\Transformers\Api\MallOrder;

use App\Models\MallOrder;
use App\Models\MallOrderEntity;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;
use App\Policies\CouponLibrary\CashCouponPolicy;

class MallOrderTransformer extends TransformerAbstract
{
    /**
     *
     * 积分商城商品数据获取和转化
     * @return array
     */
    public function transform(MallOrder $mall_order)
    {
        $item = $mall_order->item;
        $source = $item->source;
        $data = [
            'id' => $mall_order->id,
            'no' => $mall_order->no,
            'name' => $mall_order->item->name,
            'score' => $mall_order->score,
            'status' => $mall_order->status,
            'exchange_time' => Carbon::parse($mall_order->exchange_time)->format('Y-m-d'),
            'mall_type' => $mall_order->mall_type,
            'image_url' =>  $mall_order->item->product->images[0]->path ?? '',
            'http_url' => env('QINIU_URL')
//            'image_url' => count($mall_order->item->product->images) > 0 ? env('QINIU_URL') . $mall_order->item->product->images[0]->path : ''
        ];
        if ('App\Models\MallOrderCoupon' == $item->source_type) {
            $data = array_merge($data, [
                'period_start' => Carbon::parse($source->period_start)->format('Y-m-d'),
                'period_end' => Carbon::parse($source->period_end)->format('Y-m-d'),
            ]);
        }
        if (MallOrderEntity::class == $item->source_type) {
            $data = array_merge($data, [
                'specifications'=>$source->specifications
            ]);
        }
        return $data;
    }

    /*
     * 返回规则各属性
     */
    public function proper($policy_rule, $field)
    {
        if (gettype($policy_rule) == 'string') {
            $policy_rule = json_decode($policy_rule, true);
        }
        return $policy_rule[$field] ?? '';
    }
}
