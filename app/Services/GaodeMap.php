<?php

namespace App\Services;

use GuzzleHttp\Client;

class GaodeMap
{
    protected $baseUrl = 'http://restapi.amap.com';

    protected $geoApi = 'v3/geocode/regeo';

    protected $appKey;

    public $http;

    public function __construct()
    {
        $this->appKey = config('gaode.app_key');
    }

    protected function getHttp()
    {
        if (!$this->http) {
            $this->http = new Client(['base_uri' => $this->baseUrl]);
        }

        return $this->http;
    }

    /**
     * 坐标获取地理位置信息
     * gcj02ll（国测局经纬度坐标）、wgs84ll（ GPS经纬度）
     */
    public function geoInfo($lng, $lat)
    {
        $res = $this->getHttp()->request('GET', $this->geoApi, [
            'query' => [
                'location' => sprintf('%s,%s', $lng, $lat),
                'key' => $this->appKey,
                'output' => 'json',
            ],
            //'debug'=>true,
        ]);

        $data = json_decode((string)$res->getBody(), true);
        if (!is_array($data) || !$data['status']) {
            logger('location error:'.$data);
            throw new \Exception('坐标转换失败');
        }
        return $data['regeocode'];
    }
}
