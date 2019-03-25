<?php

namespace App\Transformers\Admin;

use App\Models\Coupon;
use App\Models\User;
use App\Models\CouponGrand;
use League\Fractal\TransformerAbstract;
use App\Policies\CouponLibrary\CashCouponPolicy;
use App\Models\CouponLibrary;
use Carbon\Carbon;

class CouponGrandTransformer extends TransformerAbstract
{
    /**
     * 
     * 优惠券发券记录获取和转化
     * @return array
     */
    
    public function transform(CouponGrand $coupon_grand)
    {
        $grand_all = $coupon_grand->library->count();
        $grand_num = $coupon_grand->grandNum->count();
        $coupon = $coupon_grand->coupon;
        return [
            'id' => $coupon_grand->id,
            'no' => $coupon_grand->no,
            'name' => $coupon_grand->name,
            'status' => $coupon_grand->status,
            'status_text' => $coupon_grand->statusText($coupon_grand->status),
            'scence' => $coupon_grand->scence,
            'scence_text' => $coupon_grand->scenceText($coupon_grand->scence),
            'grand_time' => (string) $coupon_grand->grand_time,
            'admin_name' => $coupon_grand->admin->name ?? '',
            'grand_all' => $grand_all,
            'grand_num' => $grand_num,
            'period_time' => !$coupon->period_type ? $coupon->period_start->format('Y-m-d') . '至' . $coupon->period_end->format('Y-m-d') :
                $coupon_grand->grand_time->format('Y-m-d') . '至' . Coupon::getTimePeriod($coupon, $coupon_grand->grand_time),
        ];
    }
}
