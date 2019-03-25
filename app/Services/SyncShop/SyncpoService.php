<?php


namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;


class SyncpoService
{
    public function sendPosRequest($data)
    {
        /**
         *
        SYNCSHOP_URI=http://link.syncrock.com/openapi/trade/orders/accept.action
        SYNCSHOP_SECRET=1dd4267a188698fbca03e83d560ce296
        SYNCSHOP_CONSUMERKEY=2FMf6HR9RDOCN0tL7QHJag
        SYNCSHOP_COMPANYOUID=plhbKeM4R3yHHHqkh513Fw
        SYNCSHOP_XPARTNER=MiUoRisor5gsrCr3Gp7T42
        SYNCSHOP_HOST=http://link.syncrock.com
         */
        $uri = env('SYNCSHOP_URI');
        //从config获取获取配置 （.env）
        $secret = env('SYNCSHOP_SECRET');

        $consumerKey = env('SYNCSHOP_CONSUMERKEY');

        $companyOuid = env('SYNCSHOP_COMPANYOUID');
        $x_partner = env('SYNCSHOP_XPARTNER');
        $timetamp = time();
        //body中的数据
        $allData = [
            'consumerKey' => $consumerKey,
            'companyOuid' => $companyOuid,
            'data' => $data,
        ];
        $dataLine = json_encode($allData);   //将body里面的数据转换为字符串进行MD5加密
        \Log::info($dataLine);
        $sign = md5($dataLine . $timetamp . $secret);
        //发送http请求包括header和body
        $client = new Client();
        $response = $client->request('POST', $uri, [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Timestamp' => $timetamp,
                'X-Partner' => $x_partner,
                'X-Sign' => $sign
            ],
            'body' => $dataLine
        ]);
        Log::info('pos_response', [$response->getBody()]);
        if ($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody(), true);
            return $result;
        }
        return false;
    }
}
