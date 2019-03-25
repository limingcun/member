<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\ApiController;
use App\Models\MallOrder;
use App\Services\JuheExp;
use App\Transformers\Api\MallOrder\MallOrderItemTransformer;
use App\Transformers\Api\MallOrder\MallOrderTransformer;

class MallOrderController extends ApiController
{
    /**
     * 兑换记录
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderList()
    {
        $user = $this->user();
        $records = $user->mallOrder()
            ->with([
                'item',
                'item.source',
                'item.product.images',
            ])
            ->orderBy('id', 'desc')
            ->paginate(config('app.page'));
        return $this->response->collection($records, new MallOrderTransformer());
    }

    /**
     * 订单详情
     */
    public function orderDetail()
    {
        $orderId = request('order_id');
        $mallOrder = MallOrder::findOrFail($orderId);
        $express = $mallOrder->express;
        if (!$traces = $express->trace) {
            $JuheExp = new JuheExp();
            $delivery = $JuheExp->query($express->shipper_code, $express->no);
            $traces = is_array($delivery) ? $delivery['list'] : [];
        }
        $traces = array_reverse($traces);
        $order = ($this->response->item($mallOrder, new MallOrderItemTransformer()))->original['data'];
        return compact('order', 'traces');
    }
}