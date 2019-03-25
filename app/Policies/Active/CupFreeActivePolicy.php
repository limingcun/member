<?php
/**
 * 满多少杯减多少杯
 * (下单前触发)
 */

namespace App\Policies\Active;


use App\Models\Active;
use App\Models\ActiveJoin;
use App\Models\Shop;
use App\Models\User;
use App\Policies\Policy;
use Carbon\Carbon;

class CupFreeActivePolicy extends Policy
{
    protected $rules = [
        'cup' => 'required|integer', //满多少杯
        'free' => 'required|integer', //减多少杯
    ];

    /**
     *  验证门店是否可以参与活动
     */
    public function shop(Active $active, Shop $shop)
    {
        return $active->shop_limit ? $active->shop->contains($shop->id) : true;
    }

    /**
     * 验证用户是否可以参与活动
     * @param User $user
     * @param Active $active
     * @return bool
     */
    public function user(Active $active, User $user)
    {
        return $active->user_limit ? $active->user->contains($user->id) : true;
    }

    /**
     * 频率验证
     * @param User $user
     * @param Active $active
     */
    public function freq(Active $active, User $user)
    {
        //总次数限制
        if ($total_freq = $active->total_freq) {
            $total = $total_freq > ActiveJoin::where('user_id', $user->id)
                    ->where('active_id', $active->id)
                    ->count();
        } else {
            $total = true;
        }
        //今天次数限制
        if ($day_freq = $active->day_freq) {
            $day = $day_freq > ActiveJoin::where('user_id', $user->id)
                    ->where('active_id', $active->id)
                    ->where('created_at', '>=', Carbon::today())
                    ->where('created_at', '<=', Carbon::tomorrow())
                    ->count();
        } else {
            $day = true;
        }
        return $total && $day;
    }
}