<?php

namespace App\Services;
use IQuery;
use JPush\Client as JPush;

class JiPush
{
    private $jpushKey;
    private $jpushSecret;
    
    public function __construct()
    {
        $this->jpushKey = env('JPUSH_KEY');
        $this->jpushSecret = env('JPUSH_SECRET');
    }

    /**
     * 极光推送传给前端
     */
    public function sendAppMsg($userId, $msg = '')
    {
        $apns = env('APP_ENV') == 'develop' ? false : true;
        $options = [
            'apns_production' => $apns
        ];
        $client = new JPush($this->jpushKey, $this->jpushSecret);
//            $client->push()->setPlatform('all')->addRegistrationId($arr)->iosNotification(
        $client->push()->setPlatform('all')->addAlias($userId)->iosNotification(
            [
                'body' => $msg
            ], 
            [
                'badge' => '+1',
                'extras' => [
                    'msgType' => 0,
                    'msgTitle' => '星球通告',
                    'msgBody' => $msg,
                    'msgDate' => \Carbon\Carbon::today()->format('Y-m-d'),
                    'msgLink' => null
                ]
            ])->options($options)->send();
    }
}