<?php

namespace App\Transformers\Admin;

use App\Models\CouponLibrary;
use League\Fractal\TransformerAbstract;
use IQuery;

class CouponLibraryTransformer extends TransformerAbstract
{
    /**
     * 
     * 会员数据获取和转化
     * @return array
     */
    public function transform(CouponLibrary $coupon_library)
    {
        return [
            'code_id' => $coupon_library->code_id,
            'status' => $this->getStatus($coupon_library->status),
            'name' => $coupon_library->name,
            'phone' => $coupon_library->phone,
            'pick_time' => (string) $coupon_library->created_at,
            'used_at' => $coupon_library->used_at,
            'no' => $coupon_library->no,
            'code' => $coupon_library->code,
            'user_id' => $coupon_library->user_id
        ];
    }
    
    public function getStatus($status) {
        switch($status) {
            case CouponLibrary::STATUS['unpick']:
                return '未领取';
            case CouponLibrary::STATUS['surplus']:
                return '已领取';
            case CouponLibrary::STATUS['used']:
                return '已使用';
            case CouponLibrary::STATUS['period']:
                return '已过期';
        }
    }
}
