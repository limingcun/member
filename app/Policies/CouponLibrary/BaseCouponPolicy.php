<?php

namespace App\Policies\CouponLibrary;


use App\Models\CouponLibrary;
use App\Policies\Policy;
use App\Models\Shop;
use App\Models\Category;
use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\Member;
use IQuery;

abstract class  BaseCouponPolicy extends Policy
{

    abstract public function usable(CouponLibrary $couponLibrary, $shopId, $items, $is_take = 0);

    abstract public function discount(CouponLibrary $couponLibrary, $items, $shopId = 0, $delivery_fee = 0);

    abstract public function discountText(CouponLibrary $couponLibrary);

    abstract public function discountUnit();

    abstract public function threshold(CouponLibrary $couponLibrary);
    
    abstract public function typeNum();

    // 能否使用优惠券的标志
    protected $flag = false;

    /**
     * is_take(0是自取,1是外卖)
     */
    protected function useLimit($use_limit, $is_take)
    {
        switch ($use_limit) {
            case CouponLibrary::USELIMIT['all']:
                return true;
            case CouponLibrary::USELIMIT['pick']:
                if ($is_take == 0) {
                    return true;
                }
                return false;
            case CouponLibrary::USELIMIT['take']:
                if ($is_take == 1) {
                    return true;
                }
                return false;
        }
    }

    /**
     * 时间是否有效
    */
    protected function dateLimit($start, $end)
    {
        $start = $start->format('Y-m-d') > Carbon::today() ? false : true;
        return $start;
    }

    /**
     *店铺是否适用
    */
    protected function shopLimit($coupon, $shopId)
    {
        if ($coupon->shop_limit) {
            $shopLimit = $coupon->shop->find($shopId) ? true : false;
        } else {
            $shopLimit = true;
        }
        return $shopLimit;
    }

    /**
     *商品是否适用
    */
    protected function productLimit($coupon, $items, &$fee)
    {
        if ($coupon->product_limit) {
            $products = $coupon->product->find(array_column($items, 'product_id'));
            $productLimit = $products->isNotEmpty();
            foreach ($items as $item) {
                if ($products->find($item['product_id'])) {
                    $fee += $item['price'] * $item['quantity'];
                }
            }
        } else {
            $productLimit = true;
            foreach ($items as $item) {
                $fee += $item['price'] * $item['quantity'];
            }
        }
        return $productLimit;
    }

    /**
     * 使用门槛
    */
    protected function ruleLimit($fee, $couponLibrary)
    {
        //价格使用门槛验证
        $ruleLimit = $fee - $couponLibrary->policy_rule['enough'] >= 0 ? true : false;
        // 总金额大于优惠券金额才能使用
        // $cutLimit = $fee - $couponLibrary->policy_rule['cut'] > 0 ? true : false;
        return $ruleLimit;
    }

    /**
     * 加料是否适用
    */
    protected function materialLimit($coupon, $items)
    {
        $material_limit = false;
        if ($coupon->material_limit) {
            $material_ids = array_column($items, 'material_ids');
            foreach($material_ids as $material_id) {
                $material = $coupon->material->find($material_id);
                if (count($material) > 0) {
                    $material_limit = true;
                    break;
                }
            }
        } else {
            $material_limit = true;
        }
        return $material_limit;
    }

    /**
     * 商品是否在归属分类内
     */
    protected function categoryLimit($coupon, $items) {
        if ($coupon->category_limit) {
            $categoryLimit = false;
            $res = $coupon->category;
            $arr = [];
            foreach($res as $r) {
                $arr = array_merge($arr, array_column($r->products->toArray(), 'id'));
            }
            foreach($items as $item) {
                if (in_array($item['product_id'], $arr)) {
                    $categoryLimit = true;
                    break;
                }
            }
        } else {
            $categoryLimit = true;
        }
        return $categoryLimit;
    }
    
    /**
     * 免运费券限制
     * $couponLibrary
     * $shopId门店id
     */
    public function deliveryLimit(CouponLibrary $couponLibrary, $shopId) {
        $policy_rule = $couponLibrary->policy_rule;
        $shop = Shop::findOrFail($shopId);
        $deliveryLimit = true;
        if ($policy_rule['cup_type'] == CouponLibrary::CUPTYPE['take'] && $shop->delivery_fee == 0) {
            $deliveryLimit = false;
        }
        return $deliveryLimit;
    }
    
    /*
     * 买赠券数量限制
     * 指定商品数量是否大于n(除去指定加料的商品)
     * $coupon优惠券模板
     * $rule优惠券规则
     * $items购买商品值
     */
    public function buyFeeLimit(Coupon $coupon, $rule, $items) {
        $buy = $rule['buy'];
        $fee = $rule['fee'];
        //除去加料数量
        $q = array_sum(array_column($items, 'quantity'));
        if ($coupon->category_limit) {
            $categorys = $coupon->category;
            $categoryProArr = [];
            foreach($categorys as $category) {
                $categoryProArr = array_merge($categoryProArr, array_column($category->products->toArray(), 'id'));
            }
            $s = $this->contrast($items, $categoryProArr); //存在商品数量
            if (bcadd($buy, $fee) > $s) {
                return false;
            }
        }
        if ($coupon->product_limit) {
            $productArr = array_column($coupon->product->toArray(), 'id');
            $s = $this->contrast($items, $productArr);
            if (bcadd($buy, $fee) > $s) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * $item数组存在于arr的数量
     * @param type $items
     * @param type $arr
     * @return int
     */
    public function contrast($items, $arr) {
        $s = 0;
        foreach($items as $item) {
            if (in_array($item['product_id'], $arr)) {
                for($i = 0; $i < $item['quantity']; $i++) {
                    $s++;
                }
            }
        }
        return $s;
    }

        /**
     * 可用时段限制
     * $couponLibrary优惠券
     */
    public function intervalTimeLimit($interval_time) {
        if ($interval_time == '1') {
            return true;
        }
        return IQuery::rangeTime($interval_time);
    }
    
    /**
     * 优惠券冻结
     */
    public function starLockLimit(CouponLibrary $couponLibrary) {
        $coupon = $couponLibrary->coupon;
        $flag = $coupon->flag;
        $member = $couponLibrary->member;
        if (in_array($flag, [Coupon::FLAG['fee_star_prime_day'], Coupon::FLAG['fee_star_birthday'], Coupon::FLAG['fee_star_anniversary'], Coupon::FLAG['fee_star_update']])) {
            $is_star = Member::isStarMember($member);
            if ($is_star) {
                return true;
            }
            return false;
        }
        return true;
    }
    
    /**
     * 优先券使用数量限制
     * $items商品
     */
    public function numberLimit($items) {
        $num = 0;
        foreach($items as $item) {
            $num += intval($item['quantity']);
        }
        if ($num > 10) {
            return false;
        }
        return true;
    }
    
    /**
     * 星球会员获取配送费折扣
     * @param Request $request
     */
    public function starDiscount(Member $member) {
        $is_star = Member::isStarMember($member);
        $discount = 1;
        if ($is_star) {
            switch($member->star_level_id) {
                case 1:  //白银
                    $discount = 1;
                    break;
                case 2:  //黄金
                    $discount = $discount * 0.9;
                    break;
                case 3:  //铂金
                    $discount = $discount * 0.7;
                    break;
                case 4:  //钻石
                    $discount = $discount * 0.5;
                    break;
                case 5:  //黑金
                    $discount = $discount * 0.3;
                    break;
                case 6:  //黑钻
                    $discount = 0;
                    break;
                default:
                    $discount = 1;
                    break;
            }
        }
        return $discount;
    }
    
    /**
     * 不可使用原因
     */
    public function unUseReason($couponLibrary, $coupon, $shopId, $items, $is_take, $fee) {
        if (!$this->dateLimit($couponLibrary->period_start, $couponLibrary->period_end)) {
            return '未到可用期限';
        }
        if (!$this->intervalTimeLimit($couponLibrary->interval_time)) {
            return '未到可用期限';
        }
        if (!$this->shopLimit($coupon, $shopId)) {
            return '当前门店不可用';
        }
        if (!$this->useLimit($couponLibrary->use_limit, $is_take)) {
            if ($couponLibrary->use_limit == 1) {
                return '仅限门店自取';
            } else if ($couponLibrary->use_limit == 2) {
                return '仅限外卖可用';
            }
        }
        if (!$this->productLimit($coupon, $items, $fee)) {
            return '仅限'.$coupon->product->pluck('name').'可用';
        }
        if (!$this->categoryLimit($coupon, $items)) {
            return '仅限'.$coupon->category->pluck('name').'可用';
        }
        if ($couponLibrary->policy == QueueCouponPolicy::class) {
            if (!$this->numberLimit($items)) {
                return '订单不可超过10杯';
            }
        }
        if ($couponLibrary->policy == CashCouponPolicy::class) {
            if (!$this->ruleLimit($fee, $couponLibrary)) {
                return '订单满'.$couponLibrary->policy_rule['enough'].'元可用';
            }
        }
        return '';
    }
}
