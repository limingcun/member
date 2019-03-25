<?php

namespace App\Services;


use GuzzleHttp\Client;

class KDNiao
{
    private $url = [
        1008 => 'http://api.kdniao.cc/api/dist',
        8001 => 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx',
        1002 => 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx',
    ];
    private $testUrl = [
        1008 => 'http://sandboxapi.kdniao.cc:8080/kdniaosandbox/gateway/exterfaceInvoke.json',
        8001 => 'http://sandboxapi.kdniao.cc:8080/kdniaosandbox/gateway/exterfaceInvoke.json',
        1002 => 'http://sandboxapi.kdniao.cc:8080/kdniaosandbox/gateway/exterfaceInvoke.json',
    ];

    private $EBusinessID = 'test1367691';
    private $appKey = '02ddcee9-c09f-466e-bd96-a437b1b29844';

    public function __construct()
    {
        if (!env('KD_DEBUG')) {
            $this->EBusinessID = env('KD_ID');
            $this->appKey = env('KD_KEY');
        }
    }

    public function request($RequestType, $requestData)
    {
        $requestData = json_encode($requestData);
        $data = array(
            'EBusinessID' => $this->EBusinessID,
            'RequestType' => $RequestType,
            'RequestData' => urlencode($requestData),
            'DataType' => '2',
        );
        $data['DataSign'] = $this->sign($requestData);
        $http = new Client();
        $env = env('KD_DEBUG') ? 'testUrl' : 'url';
        $response = $http->request('POST', $this->$env[$RequestType], [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => $data,
        ]);
        $contents = json_decode($response->getBody(), true);
        return $contents;
    }

    /**
     * 查询快递
     * @param $ShipperCode
     * @param $LogisticCode
     * @return mixed
     */
    public function query($ShipperCode, $LogisticCode)
    {
        $code = env('KD_DEBUG') ? 1002 : 8001;
        $content = $this->request(1002, [
            'ShipperCode' => $ShipperCode,
            'LogisticCode' => $LogisticCode,
        ]);
        return $content;
    }

    /**
     * 订阅快递
     */
    public function subscribe($ShipperCode, $LogisticCode)
    {
        $content = $this->request(1008, [
            'ShipperCode' => $ShipperCode,
            'LogisticCode' => $LogisticCode,
            'PayType' => 1,
            "Sender" => [
                "Name" => "喜茶",
                "Tel" => "0755-26907225",
                "Mobile" => "",
                "ProvinceName" => "广东省",
                "CityName" => "深圳市",
                "ExpAreaName" => "南山区",
                "Address" => "粤海街道海德三道以北与后海滨路以东交汇处航天科技广场B座6楼602C"
            ],
            "Receiver" => [
                "Name" => "喜茶",
                "Tel" => "0755-26907225",
                "Mobile" => "",
                "ProvinceName" => "广东省",
                "CityName" => "深圳市",
                "ExpAreaName" => "南山区",
                "Address" => "粤海街道海德三道以北与后海滨路以东交汇处航天科技广场B座6楼602C",
            ],
        ]);
        return $content;
    }

    public function sign($requestData)
    {
        return urlencode(base64_encode(md5($requestData . $this->appKey)));
    }
}