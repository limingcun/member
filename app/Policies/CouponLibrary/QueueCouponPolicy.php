<?php

namespace App\Policies\CouponLibrary;

use App\Models\CouponLibrary;
use Carbon\Carbon;

/**
 * 免排队券
 * share 1表示可共用,0表示不可共用
 * clsarr 共用类型数组：0现金券,1赠饮券,2买赠券,3折扣券
 * 优化性能需要预加载coupon，coupon.product，coupon.shop
 */
class QueueCouponPolicy extends BaseCouponPolicy
{
     protected $rules = [
        'share' => 'required',  //是否共用
        'clsarr' => 'required', //共用券类型
    ];

    /**
     * 优惠券是否可用
     */

    public function usable(CouponLibrary $couponLibrary, $shopId, $items, $is_take = 0)
    {
        $coupon = $couponLibrary->coupon;
        $fee = 0;
        try {
            $limit = $this->shopLimit($coupon, $shopId)
                     && $this->dateLimit($couponLibrary->period_start, $couponLibrary->period_end)
                     && $this->useLimit($couponLibrary->use_limit, $is_take)
                     && $this->productLimit($coupon, $items, $fee)
                     && $this->numberLimit($items)
                     && $this->intervalTimeLimit($couponLibrary->interval_time);
            return $limit;
        } catch (\Exception $e){
            \Log::error('QueueCouponPolicy->usable:' . $e->getMessage());
        }
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
        return 0;
    }

    public function discountText(CouponLibrary $couponLibrary)
    {
        return '';
    }

    public function discountUnit()
    {
        return '';
    }
    
    /*
     * 优先券类型对应4
     */
    public function typeNum() {
        return 4;
    }

    /**
     * 券的使用门槛
     */
    public function threshold(CouponLibrary $couponLibrary)
    {
        return '订单享受优先点单优先制作,新店开业前三天不支持使用';
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
        $policy_rule = $couponLibrary->policy_rule;
        $share = $policy_rule['share'];
        $clsarr = $policy_rule['clsarr'];
        return $this->queueShare($share, $clsarr);
    }
    
    /**
     * 商品显示
     * @param CouponLibrary $couponLibrary
     * @return string
     */
    public function productShow(CouponLibrary $couponLibrary) {
        return '';
    }

    /**
     * 优先券共用提示
     * @param type $share
     * @param type $clsarr
     * @return string
     */
    public function queueShare($share, $clsarr) {
        if (!$share) {
            return '不可与现金券,赠饮券,买赠券,折扣券同时使用';
        } else {
            if (in_array(0, $clsarr)) {
                $c = '现金券,';
            } else {
                $c = '';
            }
            if (in_array(1, $clsarr)) {
                $f = '赠饮券,';
            } else {
                $f = '';
            }
            if (in_array(2, $clsarr)) {
                $b = '买赠券,';
            } else {
                $b = '';
            }
            if (in_array(3, $clsarr)) {
                $d = '折扣券,';
            } else {
                $d = '';
            }
        }
        $r = '可与'.$c.$f.$b.$d;
        $r = substr($r, 0, strlen($r) - 1);
        return $r.'同时使用';
    }
}