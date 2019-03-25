<?php

namespace App\Http\Controllers\Notification;


use App\Models\MallOrder;
use App\Models\MallOrderExpress;
use App\Services\KDNiao;

class ExpressController
{

    public function test()
    {
        $notify = request()->all();
        \Log::info('EXPRESS', $notify);
        $KDNiao = new KDNiao();
        if (urldecode($KDNiao->sign($notify['RequestData'])) != $notify['DataSign']) {
            return [
                'Success' => false,
                'Reason' => '',
            ];
        }
        $content = json_decode($notify['RequestData'], true);
        if ($content['State'] == 3) {
            $express = MallOrderExpress::where('shipper_code', $content['ShipperCode'])
                ->where('no', $content['LogisticCode'])->first();
            if ($express) {
                MallOrder::where('id', $express->mall_order_id)->update([
                    'status' => MallOrder::STATUS['finish']
                ]);
            }
        }
        return [
            'EBusinessID' => $content['EBusinessID'],
            'UpdateTime' => $content['PushTime'],
            'Success' => true,
            'Reason' => '',
        ];
    }
}