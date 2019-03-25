<?php
/**
 * 聚合数据 配送
 */

namespace App\Services;


use GuzzleHttp\Client;

class JuheExp
{

    private $key = '';

    public function __construct()
    {
        $this->key = env('JUHE_KEY', '50c8196277694004270e78a3461d4c49');
    }

    public function query($com, $no)
    {
        $content = \Cache::remember('juhe_exp_' . $com . $no, 30, function () use ($com, $no) {
            return $this->request('index', [
                'com' => $com,
                'no' => $no,
            ]);
        });
        if ($content['error_code'] == 0) {
            return $content['result'];
        } else {
            \Log::error('JUHE', $content);
            return $content['reason'];
        }
    }

    public function companyList()
    {
        $content = $this->request('com', []);
        return ($content);
    }

    private function request($uri, $data)
    {
        $http = new Client();
        $response = $http->request('POST', 'http://v.juhe.cn/exp/' . $uri, [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => array_merge($data, [
                'key' => $this->key
            ]),
        ]);
        $contents = json_decode($response->getBody(), true);
        return $contents;
    }
}