<?php

namespace App\Transformers\Api;

use App\Models\CouponLibrary;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class CouponQrcodeTransformer extends TransformerAbstract
{
    public function transform(CouponLibrary $coupon_library)
    {
        $libraryPolicy = app($coupon_library->policy);
        return [
            'id' => $coupon_library->id,
            'name' => $coupon_library->name,
            'period_start' => $coupon_library->period_start->format('Y.m.d'),
            'period_end' => $coupon_library->period_end->format('Y.m.d'),
            'shop_limit' => $coupon_library->coupon->shop_limit ? $this->shopLimit($coupon_library->coupon->shop) : null,
            'product_limit' => $coupon_library->coupon->product_limit ? $this->productLimit($coupon_library->coupon->product) : null,
            'use_limit' => $coupon_library->use_limit,
            'status' => $coupon_library->status,
            'discountText' => $libraryPolicy->discountText($coupon_library) ?? null,
            'discountUnit' => $libraryPolicy->discountUnit($coupon_library) ?? null,
            'threshold' => $libraryPolicy->threshold($coupon_library) ?? null,
            'tab' => $coupon_library->tab,
            'image_url' => env('QINIU_URL'),
            'count' => $coupon_library->count ?? 0,
            'errorno' => $coupon_library->errorno ?? 0
        ];
    }
    
    public function shopLimit($limits)
    {
        return $limits->pluck('name');
    }

    public function productLimit($limits)
    {
        $product_ids = $limits->pluck('id')->toArray(); //获取全部产品id
        //判断是否是显示分类还是显示商品
        $category = [];
        $category_str = '';
        //各分类名称对应的产品id
        foreach($limits as $limit) {
            if (!array_key_exists($limit->category->name, $category)) {
                $category[$limit->category->name] = $limit->category->products->pluck('id')->toArray();
            }
        }
        foreach($category as $k => $v) {
            if (count($product_ids) > 0) {
                if (count($product_ids) >= count($v)) {
                    $product_ids = array_diff($product_ids, $v);
                    $category_str .= $k.',';
                } else {
                    return $limits->pluck('name');
                }
            }
        }
        $category_str = substr($category_str, 0, strlen($category_str)-1); 
        return $category_str;
    }
}
