<?php

namespace App\Transformers\Api;

use App\Models\MallProduct;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;
use App\Policies\CouponLibrary\CashCouponPolicy;
use App\Policies\CouponLibrary\FeeCouponPolicy;
use App\Policies\CouponLibrary\BuyFeeCouponPolicy;
use App\Policies\CouponLibrary\DiscountCouponPolicy;
use App\Policies\CouponLibrary\QueueCouponPolicy;
use App\Models\Coupon;

class MallProductTransformer extends TransformerAbstract
{
    const TYPE = [
        CashCouponPolicy::class => '现金券',
        FeeCouponPolicy::class => '赠饮券',
        BuyFeeCouponPolicy::class => '买赠券',
        DiscountCouponPolicy::class => '折扣券',
        QueueCouponPolicy::class => '优先券',
    ];
    
    const VALEN = [
        '最高价', '次高价', '次低价', '最低价'
    ];
    
    const UNITTIME = [
        '天', '月', '年'
    ];
    
    /**
     *
     * 积分商城商品数据获取和转化
     * @return array
     */
    public function transform(MallProduct $mall_product)
    {
        if ($mall_product->is_specification) {
            $store = $mall_product->skus->where('is_show',1)->sum('store');
        } else {
            $store = $mall_product->store;
        }
        $data = [
            'id' => $mall_product->id,
            'name' => $mall_product->name,
            'score' => $mall_product->score,
            'store' => $store,
            'image_url' => $mall_product->images[0]->path ?? '',
            'mall_type' => $mall_product->mall_type,
            'http_url' => env('QINIU_URL')
        ];
        return $data;
    }
}
