<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponLibrary;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use IQuery;
use Log;

/**
 * Created by PhpStorm.
 * User: heyujia
 * Date: 2018/12/18
 * Time: 上午10:18
 */
class MiniGame
{

    private $url = '';

    public function __construct()
    {
        $this->url = env('MINI_GAME_URL');
    }

    /**
     * 使用优惠券
     * @param $unionId
     * @param $couponId
     */
    public function useCoupon(\App\Models\User $user, $couponId)
    {
        try {
            if(!$user->wx_union_id) return;
            $this->request('/xicha/api/useCoupon', $user->wx_union_id, $couponId);
        } catch (GuzzleException $e) {
            Log::error('MINI_GAME_EXCEPTION', [$e]);
        }
    }

    /**
     * 查询小游戏获取的优惠券
     * @param $unionId
     * @return mixed|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function queryCoupon($unionId)
    {
//        return json_decode('{"code":"0","data":[{"id":40001,"num":20},{"id":40002,"num":10},{"id":40003,"num":10}]}', true);
        return $this->request('/xicha/api/queryCoupon', $unionId);
    }

    public function getCoupon(\App\Models\User $user)
    {
        try {
            if(!$user->wx_union_id) return;
            $unionId = $user->wx_union_id;
//            $unionId = 'oZ3J71VOG1jWVjY8c_K5ALKj4AQ8';
            $couponResult = $this->queryCoupon($unionId);
            if ($couponResult && count($couponResult['data'])) {
                $librarys = CouponLibrary::selectRaw('coupon_id,count(1) num')
                    ->where('user_id', $user->id)
                    ->whereIn('coupon_id', array_pluck($couponResult['data'], 'id'))
                    ->groupBy('coupon_id')
//                    ->whereNull('used_at')
                    ->get();
                foreach ($couponResult['data'] as $result) {
                    $library = $librarys->where('coupon_id', $result['id'])->first();
                    $num = $library ? $result['num'] - $library->num : $result['num'];
                    if ($num>0) {
                        $this->sendCoupon($result['id'], $user, $num);
                    }
                }
            }
        }  catch (GuzzleException $e) {
            Log::error('MINI_GAME_EXCEPTION', [$e]);
        }catch (Exception $exception) {
            Log::error('MINI_GAME_EXCEPTION', [$exception]);
        }
    }

    /**
     * 发送优惠券
     * @param Coupon $coupon
     * @param \App\Models\User $user
     * @param int $num
     */
    private function sendCoupon($couponId, \App\Models\User $user, $num = 1)
    {
        $coupon=\Cache::rememberForever("mini_game_coupon:$couponId",function () use ($couponId) {
            return Coupon::find($couponId);
        });
        if ($coupon) {
            for ($i = 0; $i < $num; $i++) {
                $library[] = [
                    'code_id' => $coupon->no . IQuery::strPad($user->id + $i),
                    'user_id' => $user->id,
                    'name' => $coupon->name,
                    'coupon_id' => $coupon->id,
                    'source_id' => $coupon->id,
                    'source_type' => Coupon::class,
                    'policy' => $coupon->policy,
                    'policy_rule' => json_encode($coupon->policy_rule),
                    'period_start' => !$coupon->period_type ? $coupon->period_start : Carbon::now()->format('Y-m-d'),  //period_type为固定时间和相对时间
                    'period_end' => !$coupon->period_type ? $coupon->period_end : Coupon::getTimePeriod($coupon),
                    'use_limit' => $coupon->use_limit,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'status' => CouponLibrary::STATUS['surplus'],
                    'tab' => CouponLibrary::NEWTAB['scan'],
                    'code' => null
                ];
            }
            CouponLibrary::insert($library);
        }
    }

    /**
     * @param $uri
     * @param $unionId
     * @param null $couponId
     * @return mixed|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function request($uri, $unionId, $couponId = null)
    {
        $timeStamp = date('Y-m-d H:m:s');
        $nonceStr = str_random();
        $sign = md5($unionId . $timeStamp . $nonceStr);
        $json = [
            'unionId' => $unionId,
            'nonceStr' => $nonceStr,
            'sign' => $sign,
            'timeStamp' => $timeStamp,
        ];
        if ($couponId) {
            $json['couponId']=$couponId;
        }
        Log::info("MINI_GAME_REQUEST $uri",$json);
        $client = new Client();
        $response = $client->request('POST', $this->url . $uri, [
            'json' => $json,
            'timeout' => 1,
        ]);
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        Log::info('MINI_GAME_RESPONSE',[$result]);
        return $result;
    }
}