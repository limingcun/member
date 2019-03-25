<?php

namespace App\Policies\CouponLibrary;

use App\Models\CouponLibrary;
use Illuminate\Support\Facades\Log;

/**
 * 代金券
 * 优化性能需要预加载coupon，coupon.product，coupon.shop
 */
class CashCouponPolicy extends BaseCouponPolicy
{
    protected $rules = [
        'enough' => 'required',  //满多少
        'cut' => 'required', //减多少
    ];

    /**
     * 优惠券是否可用
     */
    public function usable(CouponLibrary $couponLibrary, $shopId, $items, $is_take = 0)
    {
        $coupon = $couponLibrary->coupon;
        $fee = 0;
        try {
            $this->flag = $this->shopLimit($coupon, $shopId)
                     && $this->dateLimit($couponLibrary->period_start, $couponLibrary->period_end)
                     && $this->useLimit($couponLibrary->use_limit, $is_take)
                     && $this->productLimit($coupon, $items, $fee)
                     && $this->ruleLimit($fee, $couponLibrary)
                     && $this->intervalTimeLimit($couponLibrary->interval_time);
        } catch (\Exception $e){
            Log::error("CashCouponPolicy->usable :" . $e->getMessage());
        }
        return $this->flag;
    }
    
    /**
     * 优惠券不可使用原因
     */
    public function unUseText(CouponLibrary $couponLibrary, $shopId, $items, $is_take = 0) {
        $coupon = $couponLibrary->coupon;
        $fee = 0;
        return $this->unUseReason($couponLibrary, $coupon, $shopId, $items, $is_take, $fee);
    }

    /**
     * 计算优惠多少钱
     */
    public function discount(CouponLibrary $couponLibrary, $items, $shopId = 0, $delivery_fee = 0)
    {
        $coupon = $couponLibrary->coupon;
        //计算总价
        $fee = 0;
        if ($coupon->product_limit) {
            $products = $coupon->product->find(array_column($items, 'product_id'));
            foreach ($items as $item) {
                if ($products->find($item['product_id'])) {
                    $fee += $item['price'] * $item['quantity'];
                }
            }
        } else {
            foreach ($items as $item) {
                $fee += $item['price'] * $item['quantity'];
            }
        }
        if ($fee <= $couponLibrary->policy_rule['cut']) {
//            $discount = $fee - 0.01;
            $discount = $fee;
        } else {
            $discount = $couponLibrary->policy_rule['cut'];
        }
        return $discount;
    }

    public function discountText(CouponLibrary $couponLibrary)
    {
        $cut = $couponLibrary->policy_rule['cut'];
        return "{$cut}";
    }

    public function discountUnit()
    {
        return '元';
    }
    
    /*
     * 现金券类型对应0
     */
    public function typeNum() {
        return 0;
    }

    /**
     * 券的使用门槛
     */
    public function threshold(CouponLibrary $couponLibrary)
    {
        $order = '订单';
        if ($couponLibrary->policy_rule['enough']) {
            return $order . "满{$couponLibrary->policy_rule['enough']}元可用";
        } else {
            return '无门槛';
        }
    }
    
    /**
     * 价格限制
     * @param CouponLibrary $couponLibrary
     * @return string
     */
    public function priceLimit(CouponLibrary $couponLibrary) {
        return '';
    }
    
    /**
     * 使用说明
     * @param CouponLibrary $couponLibrary
     * @return string
     */
    public function contentText(CouponLibrary $couponLibrary) {
        return '不可与现金券,赠饮券,买赠券,折扣券同时使用';
    }
    
    /**
     * 商品显示
     * @param CouponLibrary $couponLibrary
     * @return string
     */
    public function productShow(CouponLibrary $couponLibrary) {
        return '';
    }
}
