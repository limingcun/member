<?php

namespace App\Transformers\Admin;

use App\Models\Coupon;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Policies\CouponLibrary\CashCouponPolicy;
use App\Policies\CouponLibrary\FeeCouponPolicy;
use App\Policies\CouponLibrary\BuyFeeCouponPolicy;
use App\Policies\CouponLibrary\DiscountCouponPolicy;
use App\Policies\CouponLibrary\QueueCouponPolicy;
use App\Models\CouponLibrary;
use App\Models\CouponGrand;
use App\Models\Category;
use App\Models\Product;

class CouponDetailTransformer extends TransformerAbstract
{
    const VALEN = [
        'tallest' => 0, //最高
        'taller' => 1, //次高
        'lower' => 2, //次低
        'lowest' => 3 //最低
    ];
    /**
     *
     * 优惠券数据获取和转化
     * @return array
     */
    public function transform(Coupon $coupon)
    {
        $policy_rule = $coupon->policy_rule;
        $policy = $coupon->policy;
        $dataArr = [
            'id' => $coupon->id,
            'no' => $coupon->no,
            'name' => $coupon->name,
            'count' => $coupon->count,
            'status_text' => $coupon->getStatusTextAttribute($coupon->status),
            'image' => $coupon->image,
            'created_at' => (string) $coupon->created_at,
            'admin_name' => $coupon->admin_name,
            'shop_limit' => $coupon->shop_limit ? $this->getShopAndProduct($coupon->shop) : 0,
            'product_limit' => $coupon->product_limit ? $this->getShopAndProduct($coupon->product) : 0,
            'category_limit' => $coupon->category_limit ? $this->getShopAndProduct($coupon->category) : 0,
            'use_limit' => $this->useLimit($coupon->use_limit),
            'period_time' => !$coupon->period_type ? Carbon::parse($coupon->period_start)->format('Y-m-d') . '至' . Carbon::parse($coupon->period_end)->format('Y-m-d') :
                '自发放之日起' . $coupon->period_day . Coupon::DATE[$coupon->unit_time] .'内有效',
            'active' => $this->getActive($coupon),
            'interval_time' => $coupon->interval_time == '1' ? '全天' : $coupon->interval_time
        ];
        if ($policy == CashCouponPolicy::class) {
            if (gettype($policy_rule) == 'string') {
                $policy_rule = json_decode($policy_rule, true);
            }
            if (!$policy_rule['enough']) {
                $enough = '无门槛';
            } else {
                $enough = '满'.$policy_rule['enough'].'元使用';
            }
            $dataArr = array_merge($dataArr, [
                'type' => '现金券',
                'cut' => $policy_rule['cut'],
                'enough' => $enough
            ]);
        } else if ($policy == FeeCouponPolicy::class) {
            $enough = '无门槛';
            $cup_type = $policy_rule['cup_type'];
            switch($cup_type) {
                case CouponLibrary::CUPTYPE['category']:
                    $cname = $this->getShopAndProduct($coupon->category);
                    break;
                case CouponLibrary::CUPTYPE['product']:
                    $cname = $this->getShopAndProduct($coupon->product);
                    break;
                case CouponLibrary::CUPTYPE['take']:
                    $cname = '配送费';
                    break;
                case CouponLibrary::CUPTYPE['material']:
                    $cname = $this->getShopAndProduct($coupon->material);
                    break;
                default:
                    $cname = '';
                    break;
            }
            $dataArr = array_merge($dataArr, [
                'type' => '赠饮券',
                'cup' => $policy_rule['cup'],
                'valen' => $policy_rule['valen'],
                'enough' => $enough,
                'cname' => $cname,
                'cup_type' => $cup_type
            ]);
        } else if ($policy == BuyFeeCouponPolicy::class) {
            $dataArr = array_merge($dataArr, [
                'type' => '买赠券',
                'buy' => $policy_rule['buy'],
                'fee' => $policy_rule['fee'],
                'valen' => $policy_rule['valen']
            ]);
        } else if ($policy == DiscountCouponPolicy::class) {
            $cup_type = $policy_rule['cup_type'];
            switch($cup_type) {
                case CouponLibrary::CUPTYPE['order']:
                    $cname = '订单金额';
                    break;
                case CouponLibrary::CUPTYPE['product']:
                    $cname = $this->getShopAndProduct($coupon->product);
                    break;
                case CouponLibrary::CUPTYPE['take']:
                    $cname = '配送费';
                    break;
                case CouponLibrary::CUPTYPE['material']:
                    $cname = $this->getShopAndProduct($coupon->material);
                    break;
                default:
                    $cname = '';
                    break;
            }
            $dataArr = array_merge($dataArr, [
                'type' => '折扣券',
                'discount' => $policy_rule['discount'],
                'valen' => $policy_rule['valen'],
                'cname' => $cname,
                'cup_type' => $cup_type
            ]);
        } else {
            $share = $policy_rule['share'];
            $clsarr = $policy_rule['clsarr'];
            $queue = app(QueueCouponPolicy::class);
            $content = $queue->queueShare($share, $clsarr);
            $dataArr = array_merge($dataArr, [
                'type' => '优先券',
                'content' => $content
            ]);
        }
        return $dataArr;
    }
    
    public function getActive($coupon) {
        $flag = $coupon->flag;
        switch ($flag) {
            case Coupon::FLAG['coupon']:
                return '手动发券('.$coupon->grand->no.')';
            case Coupon::FLAG['mall']:
                return '积分商城('.$coupon->mallProduct[0]->no_code.')';
            default:
                return '尚未绑定活动';
        }
    }

    /*
     * 获取门店和饮品
     */
    public function getShopAndProduct($res)
    {
        $arr = $res->pluck('name');
        $s = '';
        foreach ($arr as $a) {
            $s .= $a . ',';
        }
        $s = substr($s, 0, strlen($s) - 1);
        return $s;
    }
    
    /*
     * 截取商品或分类名称
     */
    public function getProductCategory($res) {
        $s = '';
        foreach ($arr as $a) {
            $s .= $a . ',';
        }
        $s = substr($s, 0, strlen($s) - 1);
        return $s;
    }

    /*
     * 获取使用场景
     */
    public function useLimit($use_limit)
    {
        switch ($use_limit) {
            case Coupon::USELIMIT['self']:
                return '自取';
            case Coupon::USELIMIT['takeout']:
                return '外卖';
            default:
                return '不限';
        }
    }
    
    /*
     * 价格杯型
     */
    public function priceType($valen)
    {
        if (!$valen) {
            return '';
        }
        switch ($valen) {
            case self::VALEN['tallest']:
                return '最高价';
            case self::VALEN['taller']:
                return '次高价';
            case self::VALEN['lower']:
                return '次低价';
            default:
                return '最低价';
        }
    }
}
