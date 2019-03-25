<?php

namespace App\Http\Controllers;

use EasyWeChat\Factory;

class WechatController extends Controller
{
    public $app;
    protected function options() {
        return [
            'app_id'             => env('WECHAT_PAYMENT_APPID'),
            'mch_id'             => env('WECHAT_PAYMENT_MCH_ID'),
            'key'                => env('WECHAT_PAYMENT_KEY'),   // API 密钥
            'cert_path'          => env('WECHAT_PAYMENT_CERT_PATH'),
            'key_path'           => env('WECHAT_PAYMENT_KEY_PATH'),
//            'app_id'             => env('SUB_WECHAT_PAYMENT_APPID'),
//            'mch_id'             => env('SUB_WECHAT_PAYMENT_MCH_ID'),
//            'key'                => env('SUB_WECHAT_PAYMENT_KEY'),   // API 密钥
//            'cert_path'          => env('SUB_WECHAT_PAYMENT_CERT_PATH'),
//            'key_path'           => env('SUB_WECHAT_PAYMENT_KEY_PATH'),
            'notify_url'         => env('APP_URL') . '/notification/wechat/notify',     // 回调地址
        ];
    }

    public function __construct()
    {
        $this->app = Factory::payment($this->options());
    }

    // 统一下单
    public function payment($data)
    {
        return $this->app->order->unify($data);
    }

    // 根据商户订单号查询
    public function queryByOutTradeNumber($out_trade_no)
    {
        return $this->app->order->queryByOutTradeNumber($out_trade_no);
    }

    // 根据商户订单号查询
    public function queryByTransactionId($transaction_id)
    {
        return $this->app->order->queryByTransactionId($transaction_id);
    }

    // 关闭订单
    public function close($out_trade_no)
    {
        return $this->app->order->close($out_trade_no);
    }

    public function jssdk($prepayId, $json = false)
    {
        return $this->app->jssdk->bridgeConfig($prepayId, $json);
    }

    // 退款  商户订单号、商户退款单号、订单金额、退款金额、其他参数
    public function refund($out_trade_no, $refund_no, $totalFee, $refundFee, $config = [])
    {
        return $this->app->refund->byOutTradeNumber($out_trade_no, $refund_no,  $totalFee,  $refundFee, $config);
    }

}
