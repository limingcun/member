<?php

namespace App\Services\CashStorage;

use GuzzleHttp\Client;
use Log;
use App\Exceptions\ValidateException;
use GuzzleHttp\Exception\RequestException;
use IQuery;

class Request
{
    protected $url = 'https://wallet-test.ipaynow.cn';
//    protected $url = 'https://istore.ipaynow.cn';



    /**
     * 调取储值接口
     * @return type
     */
    public function resultFunction($data) {
        $sign = $this->sign($this->str($data));
        $data['sign'] = $sign;
        $result = $this->post($data);
        return $result;
    }

    /**
     * 
     * @param array $data
     * @return type
     */
    public function str(Array $data)
    {
        $tmp = [];
        ksort($data);
        foreach ($data as $key => $item) {
            $tmp[] = $key . '=' . $item;
        }
        return implode('&', $tmp);
    }
    
    /**
     * 验证签名
     * @param type $data
     * @return type
     */
    public function sign(String $data) {
        $privateKey = file_get_contents(app_path('Services/CashStorage/pri.pem'));
//        $privateKey = file_get_contents(app_path('Services/CashStorage/pro_pri.key'));
        $private_key = openssl_pkey_get_private($privateKey);
        $sign = $this->privateEncrypt($data, $private_key);
        return $sign;
    }
    
    /**
     * 获取私钥
     * @param type $data
     * @param type $private_key
     * @return type
     */
    public function privateEncrypt($data, $private_key) {
        openssl_sign($data, $encrypted, $private_key);
        return base64_encode($encrypted);
    }

    public function post($data)
    {   
        $client = new Client();
        try {
            $response = $client->request('POST', $this->url, [
                'json' => $data,
                'timeout' => 3
            ]);
            if ($response->getStatusCode() == 200) {
                $result = json_decode($response->getBody(), true);
                return $result;
            }
            return false;
        } catch (\Exception $e) {
            Log::info('WALLET_DATA_ERROR', [$e]);
            return false;
        }
    }
    
    public function getSign($data) {
        $sign = $this->sign($this->str($data));
        return $sign;
    }
}