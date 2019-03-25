<?php

namespace App\Transformers\Admin;

use App\Models\MallOrder;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class MallOrderTransformer extends TransformerAbstract
{
    /**
     *
     * 积分商城订单数据获取和转化
     * @return array
     */
    public function transform(MallOrder $mall_order)
    {
        $data = [
            'id' => $mall_order->id,
            'user_id' => $mall_order->user_id,
            'no' => $mall_order->no,
            'exchange_time' => $mall_order->exchange_time,
            'name' => $mall_order->item->name,
            'user_name' => $mall_order->user->name ?? '',
            'user_phone' => $mall_order->user->phone ?? '',
            'score' => $mall_order->score,
            'status_text' => $this->getStatusText($mall_order->status),
            'status' => $mall_order->status,
            'is_express' => $mall_order->is_express,
            'mall_type' => $mall_order->mall_type,
            'reason' => $mall_order->remark
        ];
        if ($mall_order->mall_type == MallOrder::MALLTYPE['real']) {
            $image_url = '';
            if ($mall_order->item->product->images->isNotEmpty()) {
                $image_url = env('QINIU_URL').$mall_order->item->product->images[0]->path;
            }
            $data = array_merge($data, [
                'image_url' =>  $image_url,
                'express' => $mall_order->express,
                'specifications' => $mall_order->item->source->specifications,
            ]);
        }
        return $data;
    }
    /*
     * 前端状态显示数据
     */
    public function getStatusText($status) {
        switch($status) {
            case MallOrder::STATUS['success']:
                return '兑换成功';
            case MallOrder::STATUS['fail']:
                return '兑换失败';
            case MallOrder::STATUS['wait_dispatch']:
                return '待发货';
            case MallOrder::STATUS['dispatching']:
                return '已发货';
            case MallOrder::STATUS['finish']:
                return '已完成';
            case MallOrder::STATUS['refund']:
                return '已退单';
            default:
                return null;
        }
    }
}
