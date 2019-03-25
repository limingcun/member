<?php

namespace App\Policies\CouponLibrary;

use App\Models\Coupon;
use App\Models\CouponLibrary;
use Illuminate\Support\Facades\DB;
use IQuery;
use App\Models\Shop;

/**
 * 折扣券
 * 优化性能需要预加载coupon，coupon.product，coupon.shop
 */
class DiscountCouponPolicy extends BaseCouponPolicy
{
    protected $rules = [
        'valen' => 'required',  //折扣规则
        'discount' => 'required', //打多少折 0.1-9.9
        'cup_type' => 'required'  //优惠对象
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
                && $this->categoryLimit($coupon, $items)
                && $this->materialLimit($coupon, $items)
                && $this->productLimit($coupon, $items, $fee)
                && $this->deliveryLimit($couponLibrary, $shopId)
                && $this->intervalTimeLimit($couponLibrary->interval_time);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
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
        /*
         * 计算参与折扣的订单价格
         *      限定商品时   计算订单中含有限定商品的总价
         *      所有商品可用  计算订单中的总价
         * 返回折扣金额
         * */
        // 订单金额 配送费 指定类别 指定商品 指定加料
        $coupon = $couponLibrary->coupon;
        $policy_rule = $couponLibrary->policy_rule;
        $valen = $policy_rule['valen'];
        $cup_type = $policy_rule['cup_type'];
        if ($coupon->id == 104) {
            \Log::info('FLAG_COUPON_POLICY_RULE', [$policy_rule]);
        }
        switch($cup_type) {
            case CouponLibrary::CUPTYPE['order']:
                if ($coupon->id == 104) {
                    \Log::info('FLAG_COUPON order', [$policy_rule]);
                }
                $discount_fee = $this->price($items, $valen, true);
                break;
            case CouponLibrary::CUPTYPE['product']:
                $discount_fee = $this->getProductLimitPrice($coupon, $valen, $items);
                break;
            case CouponLibrary::CUPTYPE['take']:
//                $shop = Shop::findOrFail($shopId);
//                $discount_fee = $shop->delivery_fee ?? 5;
                $discount_fee = $delivery_fee;
//                $discount_rate = $this->starDiscount($couponLibrary->member);
//                $discount_fee = $discount_fee * $discount_rate;
                break;
            case CouponLibrary::CUPTYPE['material']:
                $discount_fee = $this->getMaterialLimitPrice($coupon, $valen, $items);
                break;
            case CouponLibrary::CUPTYPE['discount_category']:
                if ($coupon->id == 104) {
                    \Log::info('FLAG_COUPON discount_category', [$policy_rule]);
                }
                $discount_fee = $this->getCategoryLimitPrice($coupon, $valen, $items);
                if ($coupon->id == 104) {
                    \Log::info('FLAG_COUPON discount_fee == '.$discount_fee);
                    \Log::info('FLAG_COUPON discount_fee return == '.round((1 - round($couponLibrary->policy_rule['discount'], 2) * 0.1) * $discount_fee, 2));
                }
                break;
            default:
                $discount_fee = 0;
                break;
        }
        return round((1 - round($couponLibrary->policy_rule['discount'], 2) * 0.1) * $discount_fee, 2);
    }

    public function discountText(CouponLibrary $couponLibrary)
    {
        $discount = $couponLibrary->policy_rule['discount'];
        return $discount;
    }

    public function discountUnit()
    {
        return '折';
    }

    /*
     * 折扣券类型对应3
     */
    public function typeNum() {
        return 3;
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
                $v = '订单金额';
                break;
            case 1:
                $v =  '指定商品';
                break;
            case 2:
                $v = '配送费';
                break;
            case 3:
                $v = '指定加料';
                break;
            case 4:
                $v = '指定分类';
                break;
            default:
                $v = '';
                break;
        }
        return $v.'优惠可用';
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

    /**
     * 商品价格限制
     * @param CouponLibrary $couponLibrary
     * @return string
     */
    public function priceLimit(CouponLibrary $couponLibrary) {
        $policy_rule = $couponLibrary->policy_rule;
        $cup_type = $policy_rule['cup_type'];
        $valen = $policy_rule['valen'];
        if ($cup_type == 1 || $cup_type == 3 || $cup_type == 4) {
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
            return '可折扣'.$v.'的商品';
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
                $v = '订单金额';
                break;
            case 1:
                $v =  $this->couponLimit($coupon->product);
                break;
            case 2:
                $v = '配送费';
                break;
            case 3:
                $v = $this->couponLimit($coupon->material);
                break;
            case 4:
                $v = $this->couponLimit($coupon->category);
                break;
            default:
                $v = '';
                break;
        }
        return $v;
    }

    /**
     * 折扣规则 取最高次高次低最低
     */
    public function getPrice($items, $valen)
    {
        $items = collect($items)->sortBy("price")->values()->all();
        switch ($valen) {
            case CouponLibrary::VALEN['highest']:
                return $items[count($items)-1]["price"];
            case CouponLibrary::VALEN['higher']:
                return $items[count($items)-2]["price"];
            case CouponLibrary::VALEN['lower']:
                return $items[1]["price"];
            case CouponLibrary::VALEN['lowest']:
                return $items[0]["price"];
            default:
                return 0;
        }
    }

    /**
     * 计算折扣金额
     * 订单中商品数量大于等于两个才会应用 最次高低价的规则
    */
    public function price($items, $valen, $is_order=false)
    {
        $price = 0;
        if ($is_order) {
            // 折扣整个订单
            foreach ($items as $item) {
                $price += $item['price'] * $item['quantity'];
            }
            \Log::info('DISCOUNT_ORDER ', [$items]);
        } else {
            if (count($items) > 1) {
                $price = $this->getPrice($items, $valen);
                \Log::info('DISCOUNT_PRODUCT count > 1 ', [$items]);
            } else if (count($items) == 1) {
                // 同一个商品数量为多个时 只计算一个商品
                $price = $items[0]["price"];
                \Log::info('DISCOUNT_PRODUCT count = 1 ', [$items]);
            }
        }
        return $price;
    }

    /**
     * 得到订单内限定分类商品参与折扣的总金额
     */
    public function getCategoryLimitPrice(Coupon $coupon, $valen, $items)
    {
        $res = $coupon->category->load(['products' => function($query) {
            $query->select('id', 'category_id');
        }]);
        $product_ids = [];
        foreach($res as $r) {
            $product_ids = array_merge($product_ids, array_column($r->products->toArray(), 'id'));
        }
        $limit_items = [];
        foreach($items as $item) {
            if (in_array($item['product_id'], $product_ids)) {
                $limit_items[] = $item;
            }
        }
        if ($coupon->id == 104) {
            \Log::info('FLAG_COUPON discount_category items', [$items]);
            \Log::info('FLAG_COUPON discount_category product_ids', [$product_ids]);
            \Log::info('FLAG_COUPON discount_category limit_items', [$limit_items]);
        }
        $price = $this->price($limit_items, $valen);
        return $price;
    }

    /**
     * 返回订单内限定商品参与折扣的总金额
    */
    public function getProductLimitPrice(Coupon $coupon, $valen, $items)
    {
        $products = $coupon->product->find(array_column($items, 'product_id'));
        $limit_items = array();
        foreach ($items as $item) {
            if ($products->find($item['product_id'])) {
                $limit_items[] = $item;
            }
        }
        $price = $this->price($limit_items, $valen);
        return $price;
    }

    /**
     * 返回订单中指定加料商品参与折扣的总金额
    */
    public function getMaterialLimitPrice(Coupon $coupon, $valen, $items)
    {
        $mres = $coupon->material;
        $material_ids = [];
        foreach($mres as $m) {
            $material_ids[] = $m->pivot->material_id;
        }
        $materials = [];
        foreach($items as $item) {
            $material_id = array_intersect($item['material_ids'], $material_ids);
            if (count($material_id)) {
                $material = DB::table('materials')->whereIn('id', $material_id)->where('is_actived', 1)
                    ->whereNull('deleted_at')->select('price')->get();
                //订单中加料的价格和数量数据，用于计算参与折扣的加料的价格
                foreach ($material as $m) {
                    $materials[] = ['price' => $m->price, 'quantity' => 1];
                }
            }
        }
        $price = $this->price($materials, $valen);
        return $price;
    }
}
