<?php

namespace App\Transformers\Admin;

use App\Models\Coupon;
use App\Models\User;
use App\Models\CouponLibrary;
use App\Models\CouponGrand;
use League\Fractal\TransformerAbstract;
use App\Policies\CouponLibrary\CashCouponPolicy;
use Carbon\Carbon;
use App\Policies\CouponLibrary\FeeCouponPolicy;
use App\Policies\CouponLibrary\BuyFeeCouponPolicy;
use App\Policies\CouponLibrary\DiscountCouponPolicy;
use App\Policies\CouponLibrary\QueueCouponPolicy;

class CouponTransformer extends TransformerAbstract
{
    const POLICY = [
        CashCouponPolicy::class => '现金券',
        FeeCouponPolicy::class => '赠饮券',
        BuyFeeCouponPolicy::class => '买赠券',
        DiscountCouponPolicy::class => '折扣券',
        QueueCouponPolicy::class => '优先券'
    ];
    /**
     * 
     * 优惠券数据获取和转化
     * @return array
     */
    
    public function transform(Coupon $coupon)
    {
        return [
            'id' => $coupon->id,
            'no' => $coupon->no,
            'name' => $coupon->name,
            'count' => $coupon->count,
            'type' => $coupon->policy = self::POLICY[$coupon->policy],
            'status' => $coupon->status,
            'status_text' => $coupon->getStatusTextAttribute($coupon->status),
            'image' => $coupon->image,
            'created_at' => (string) $coupon->created_at,
            'cut_status' => !$coupon->flag ? 0 : 1
        ];
    }
}
