<?php

namespace App\Services;
use IQuery;

class QrCode
{
    const TOKENURL = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential';
    const CODEURL = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode';
    const SMALLURL = 'https://api.weixin.qq.com/wxa/getwxacode';

    /**
     * @var EasyWeChat\MiniProgram\Application
     */
    private $appid;
    private $secret;
    
    public function __construct()
    {
        $this->appid = config('wechat.mini_program.app_id');
        $this->secret = config('wechat.mini_program.secret');
    }
    
    /**
     * 二维码测试生成
     */
    public function codeTestCreate()
    {
        $tokenUrl = self::TOKENURL.'&appid='. $this->appid. '&secret='. $this->secret;
        $getArr = array();
        $tokenArr=json_decode(IQuery::send_post($tokenUrl, $getArr, 'GET'));
        $access_token = $tokenArr->access_token;
        $path = '/pages/migration-project/migration-project';
        $width = 430;
        $post_data = '{"path":"'.$path.'","width":'.$width.'}';
        $url = self::CODEURL.'?access_token='. $access_token;
        $result = IQuery::api_notice_increment($url, $post_data);
        return $result;
    }

    /**
     * 二维码生成
     */
    public function codeCreate($id)
    {
        $tokenUrl = self::TOKENURL.'&appid='. $this->appid. '&secret='. $this->secret;
        $getArr = array();
        $tokenArr=json_decode(IQuery::send_post($tokenUrl, $getArr, 'GET'));
        $access_token = $tokenArr->access_token;
        $path = 'pages/index/index?qrcode=qrcode&coupon_id=' .$id;
        $width = 430;
        $post_data = '{"path":"'.$path.'","width":'.$width.'}';
        $url = self::CODEURL.'?access_token='. $access_token;
        $result = IQuery::api_notice_increment($url, $post_data);
        return $result;
    }
    
    /**
     * 小程序码生成
     */
    public function smallCodeCreate()
    {
        $tokenUrl = self::TOKENURL.'&appid='. $this->appid. '&secret='. $this->secret;
        $getArr = array();
        $tokenArr=json_decode(IQuery::send_post($tokenUrl, $getArr, 'GET'));
        $access_token = $tokenArr->access_token;
        $path = 'pages/qrRouter/qrRouter?a=1';
        $width = 430;
        $post_data = '{"path":"'.$path.'","width":'.$width.'}';
        $url = self::SMALLURL.'?access_token='. $access_token;
        $result = IQuery::api_notice_increment($url, $post_data);
        return $result;
    }
}