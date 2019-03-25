<?php

namespace App\Policies\CouponLibrary;

use App\Models\CouponLibrary;
use App\Models\Coupon;
use App\Models\Product;
use Carbon\Carbon;
use App\Models\Shop;
use IQuery;

/**
 * 赠饮券
 * 优化性能需要预加载coupon，coupon.product，coupon.shop
 */
class FeeCouponPolicy extends BaseCouponPolicy
{
    protected $rules = [
        'cup' => 'required',  //赠多少杯
        'cup_type' => 'required'  //指定哪种类型的饮品
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
                     && $this->categoryLimit($coupon, $items)
                     && $this->materialLimit($coupon, $items)
                     && $this->starLockLimit($couponLibrary)
                     && $this->deliveryLimit($couponLibrary, $shopId)
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
        $cup_type = $policy_rule['cup_type'];
        switch($cup_type) {
            case CouponLibrary::CUPTYPE['category']:
                $discount_fee = $this->isCupCategoryType($coupon, $policy_rule, $items);
                $discount_fee = $this->sumDiscountPrice($items, $discount_fee);
                break;
            case CouponLibrary::CUPTYPE['product']:
                $discount_fee = $this->isCupProductType($coupon, $items);
                $discount_fee = $this->sumDiscountPrice($items, $discount_fee);
                break;
            case CouponLibrary::CUPTYPE['take']:
//                $shop = Shop::findOrFail($shopId);
//                $discount_fee = $shop->delivery_fee ?? 5;
                $discount_fee = $delivery_fee;
//                $discount_rate = $this->starDiscount($couponLibrary->member);
//                $discount_fee = $discount_fee * $discount_rate;
                break;
            case CouponLibrary::CUPTYPE['material']:
                $discount_fee = $this->isCupMaterialType($coupon, $items);
                break;
            default:
                $discount_fee = 0;
                break;
        }
        return round($discount_fee, 2);
    }
    
    /**
     * 计算指定分类或饮品折扣
     * @param $items商品数组
     * @return string
     */
    public function sumDiscountPrice($items, $discount_fee) {
        $product_price = 0;
        foreach($items as $item) {
            $product_price += $item['quantity'] * $item['price'];
        }
        if ($product_price <= $discount_fee) {
//            $discount_fee =round($product_price - 0.01, 2);
            $discount_fee = $product_price;
        }
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
     * 赠饮券类型对应1
     */
    public function typeNum() {
        return 1;
    }

    /**
     * 券的使用门槛
     */
    public function threshold(CouponLibrary $couponLibrary)
    {
        $coupon = $couponLibrary->coupon;
        $policy_rule = $couponLibrary->policy_rule;
        $cup_type = $policy_rule['cup_type'];
        switch($cup_type) {
            case 0:
                return '可抵扣指定分类中的任一商品';
            case 1:
                return '可抵扣指定商品';
            case 2:
                return '可抵扣配送费';
            case 3:
                return '可抵扣指定加料';
            default:
                return '';
        }
    }
    
    /**
     * 商品价格限制
     * @param CouponLibrary $couponLibrary
     * @return string
     */
    public function priceLimit(CouponLibrary $couponLibrary) {
        $policy_rule = $couponLibrary->policy_rule;
        $cup_type = $policy_rule['cup_type'];
        $valen = $policy_rule['valen'];
        if ($cup_type == 0) {
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
        } else {
            $v = '';
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
        $coupon = $couponLibrary->coupon;
        $policy_rule = $couponLibrary->policy_rule;
        $cup_type = $policy_rule['cup_type'];
        switch($cup_type) {
            case 0:
                return $this->couponLimit($coupon->category).'分类中的任一商品';
            case 1:
                return $this->couponLimit($coupon->product);
            case 2:
                return '配送费';
            case 3:
                return $this->couponLimit($coupon->material);
            default:
                return '';
        }
    }


    /**
     * 商品限制
     */
    public function couponLimit($limits) {
        $arr = $limits->pluck('name');
        $s = '';
        foreach ($arr as $a) {
            $s .= $a . ',';
        }
        $s = substr($s, 0, strlen($s) - 1);
        return IQuery::filterEmoji($s);
    }
    
    /*
     * 获取类型折扣那杯(返回折扣金额)
     * $coupon优惠券模板
     * $items小程序端传过来数组集
     */
    public function isCupCategoryType(Coupon $coupon, $rule, $items) {
        if (!$coupon->category_limit) {
            $priceArr = $this->allItemProduct($items);
        } else {
            $res = $coupon->category->load(['products' => function($query) {
                $query->select('id', 'category_id');
            }]);
            $product_ids = [];
            foreach($res as $r) {
                $product_ids = array_merge($product_ids, array_column($r->products->toArray(), 'id'));
            }
            $priceArr = [];
            foreach($items as $item) {
                $product_id = $item['product_id'];
                if (in_array($product_id, $product_ids)) {
                    for($i = 0; $i < $item['quantity']; $i++) {
                        $priceArr[] = $item['price'];
                    }
                }
            }
        }
        if (count($priceArr) > 0) {
            $valen = $rule['valen'];
            return $this->hightPrice($valen, $priceArr);
        }
        return 0;
    }
    
    /**
     * 全部产品赠饮
     */
    public function allItemProduct($items) {
        $priceArr = [];
        foreach($items as $item) {
            for($i = 0; $i < $item['quantity']; $i++) {
                $priceArr[] = $item['price'];
            }
        }
        return $priceArr;
    }


    /*
     * 获取饮品折扣优惠那杯(返回折扣金额)
     * $coupon优惠券模板
     * $items小程序端传过来数组集
     */
    public function isCupProductType(Coupon $coupon, $items) {
        if (!$coupon->product_limit) {
            return 0;
        }
        $pres = $coupon->product;
        $product_ids = [];
        foreach($pres as $s) {
            $product_ids[] = $s->pivot->product_id;
        }
        $price = 0;
        foreach($items as $item) {
            $product_id = $item['product_id'];
            if (in_array($product_id, $product_ids)) {
                $price = $item['price'];
            }
        }
        return $price;
    }
    
    /*
     * 获取加料折扣
     * $coupon优惠券模板
     * $items小程序端传过来数组集
     */
    public function isCupMaterialType(Coupon $coupon, $items) {
        if (!$coupon->material_limit) {
            return 0;
        }
        $mres = $coupon->material;
        foreach($mres as $m) {
            $material_id = $m->pivot->material_id;
        }
        $price = 0;
        foreach($items as $item) {
            $material_ids = $item['material_ids'];
            if (in_array($material_id, $material_ids)) {
                $material = \DB::table('materials')->where('id', $material_id)->where('is_actived', 1)->whereNull('deleted_at')->select('price')->first();
                $price = $material->price;
                break;
            }
        }
        return $price;
    }
    
    /*
     * 判断第几高价格
     * $valen:0最高价,1次高价,2次低价,3最低价
     */
    public function hightPrice($valen, $priceArr) {
        switch($valen) {
            case CouponLibrary::VALEN['highest']:
                rsort($priceArr);
                return $priceArr[0];
            case CouponLibrary::VALEN['higher']:
                rsort($priceArr);
                $delPriceArr = $this->arrayDelete($priceArr, $priceArr[0]);
                if (count($delPriceArr) > 0) {
                    return $delPriceArr[0];
                }
                return $priceArr[0];
            case CouponLibrary::VALEN['lower']:
                sort($priceArr);
                $delPriceArr = $this->arrayDelete($priceArr, $priceArr[0]);
                if (count($delPriceArr) > 0) {
                    return $delPriceArr[0];
                }
                return $priceArr[0];
            case CouponLibrary::VALEN['lowest']:
                sort($priceArr);
                return $priceArr[0];
            default:
                return 0;
        }
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
}
