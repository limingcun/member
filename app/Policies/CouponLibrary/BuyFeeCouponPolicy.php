<?php

namespace App\Policies\CouponLibrary;

use App\Models\CouponLibrary;
use App\Models\Coupon;
use Carbon\Carbon;

/**
 * 买 M 送 N 券
 * 优化性能需要预加载coupon，coupon.product，coupon.shop
 */
class BuyFeeCouponPolicy extends BaseCouponPolicy
{
    protected $rules = [
        'buy' => 'required',  //满多少
        'fee' => 'required', //送多少
        'valen' => 'required' //价格类型
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
                     && $this->categoryLimit($coupon, $items)
                     && $this->productLimit($coupon, $items, $fee)
                     && $this->buyFeeLimit($coupon, $couponLibrary->policy_rule, $items)
                     && $this->intervalTimeLimit($couponLibrary->interval_time);
            return $limit;
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
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
        $coupon = $couponLibrary->coupon;
        $policy_rule = $couponLibrary->policy_rule;
        $discount_fee = $this->getDiscountFee($coupon, $policy_rule, $items);
        return $discount_fee;
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
     * 买赠券类型对应2
     */
    public function typeNum() {
        return 2;
    }

    /**
     * 券的使用门槛
     */
    public function threshold(CouponLibrary $couponLibrary)
    {
        $policy_rule = $couponLibrary->policy_rule;
        $buy = $policy_rule['buy'];
        $fee = $policy_rule['fee'];
        return '满'.$buy.'件商品赠送'.$fee.'件';
    }
    
    /**
     * 价格限制
     * @param CouponLibrary $couponLibrary
     * @return string
     */
    public function priceLimit(CouponLibrary $couponLibrary) {
        $policy_rule = $couponLibrary->policy_rule;
        $valen = $policy_rule['valen'];
        switch($valen) {
            case 0:
                $v = '最高价';
                break;
            case 1:
                $v = '次高价';
                break;
            case 2:
                $v = '次低价';
                break;
            case 3:
                $v = '最低价';
                break;
            default:
                $v = '';
                break;
        }
        if ($v != '') {
            return '可赠送'.$v.'的商品';
        }
        return $v;
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
    
    /*
     * 买赠计算折扣金额
     * $coupon优惠券模板
     * $rule优惠券规则
     * $items购买商品值
     */
    public function getDiscountFee(Coupon $coupon, $rule, $items) {
        $fee = $rule['fee'];
        $valen = $rule['valen'];
        $priceArr = [];
        $productArr = [];
        if ($coupon->category_limit) {
            $categorys = $coupon->category;
            foreach($categorys as $category) {
                $productArr = array_merge($productArr, array_column($category->products->toArray(), 'id'));
            }
            $priceArr = $this->priceItemArr($items, $productArr);
        } else if ($coupon->product_limit) {
            $productArr = array_column($coupon->product->toArray(), 'id');
            $priceArr = $this->priceItemArr($items, $productArr);
        } else {
            foreach($items as $item) {
                for($i = 0; $i < $item['quantity']; $i++) {
                    $priceArr[] = $item['price'];
                }
            }
        }
        $countArr = count($priceArr);
        if ($countArr >= $fee) {
            switch($valen) {
                case CouponLibrary::VALEN['highest']:
                    rsort($priceArr);
                    return $this->arraySumTab($priceArr, 0, $fee);
                case CouponLibrary::VALEN['higher']:
                    rsort($priceArr);
                    return $this->sumArr($priceArr, $fee);
                case CouponLibrary::VALEN['lower']:
                    sort($priceArr);
                    return $this->sumArr($priceArr, $fee);
                case CouponLibrary::VALEN['lowest']:
                    sort($priceArr);
                    return $this->arraySumTab($priceArr, 0, $fee);
                default:
                    return 0;
            }
        }
        return 0;
    }
    
    /**
     * 可使用的价格
     * @param type $items
     * @param type $productArr
     * @return type
     */
    public function priceItemArr($items, $productArr) {
        $priceArr = [];
        foreach($items as $item) {
            if (in_array($item['product_id'], $productArr)) {
                for($i = 0; $i < $item['quantity']; $i++) {
                    $priceArr[] = $item['price'];
                }
            }
        }
        return $priceArr;
    }


    /*
     * 数组指定求和
     * $arr数组
     * $start开始键
     * $end结束键
     * @return返回总值
     */
    public function arraySumTab($arr, $start, $end) {
        $sum = 0;
        for($i = $start; $i < $end; $i++) {
            $sum += $arr[$i];
        }
        return $sum;
    }
    
    /*
     * 删除数组中指定的数值
     * $arr数组
     * $val值
     * @return返回删除后的数组
     */
    public function arrayDelete($arr, $val) {
        $asr = [];
        for($i = 0; $i < count($arr); $i++) {
            if ($arr[$i] != $val) {
                $asr[] = $arr[$i];
            }
        }
        return $asr;
    }
    
    /*
     * 返回次合并数组数值
     * $priceArr排序好的价格数组
     * $fee赠送杯数
     * @return返回价格
     */
    public function sumArr($priceArr, $fee) {
        $parr = $this->arrayDelete($priceArr, $priceArr[0]);
        $c = count($parr);
        if ($c >= $fee) {
            return $this->arraySumTab($parr, 0, $fee);
        } else {
            return array_sum($parr) + ($fee-$c) * $priceArr[0];
        }
    }
}