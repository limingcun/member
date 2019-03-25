<?php

namespace App\Transformers\Admin;

use Carbon\Carbon;
use App\Models\CouponLibrary;
use App\Models\Coupon;
use League\Fractal\TransformerAbstract;
use App\Policies\CouponLibrary\CashCouponPolicy;
use App\Policies\CouponLibrary\FeeCouponPolicy;
use App\Policies\CouponLibrary\BuyFeeCouponPolicy;
use App\Policies\CouponLibrary\DiscountCouponPolicy;
use App\Policies\CouponLibrary\QueueCouponPolicy;


class CouponLibraryItemTransformer extends TransformerAbstract
{
    const POLICY = [
        CashCouponPolicy::class => '现金券',
        FeeCouponPolicy::class => '赠饮券',
        BuyFeeCouponPolicy::class => '买赠券',
        DiscountCouponPolicy::class => '折扣券',
        QueueCouponPolicy::class => '优先券'
    ];
    
    const VALEN = [
        '最高价', '次高价', '次低价', '最低价'
    ];
    
    /**
     *
     * 优惠券数据获取和转化
     * @return array
     */
    public function transform(CouponLibrary $coupon_library)
    {
        $coupon = $coupon_library->coupon;
        $dataArr = [
            'id' => $coupon_library->id,
            'code_id' => $coupon_library->code_id,
            'name' => $coupon_library->name,
            'user_name' => $coupon_library->user->name ?? '',
            'type' => self::POLICY[$coupon_library->policy],
            'status' => $this->statusText($coupon_library->status),
            'period_time' => Carbon::parse($coupon_library->period_start)->format('Y-m-d').'至'.Carbon::parse($coupon_library->period_end)->format('Y-m-d'),
            'pick_time' => (string) $coupon_library->created_at,
            'admin_name' => $coupon->admin_name ?? '',
            'shop_limit' => $coupon->shop_limit ? $this->getShopAndProduct($coupon->shop) : 0,
            'product_limit' => $coupon->product_limit ? $this->getShopAndProduct($coupon->product) : 0,
            'use_limit' => $coupon->use_limit ? $this->useLimit($coupon->use_limit) : '全部可用',
        ];
        $policy_rule = $coupon_library->policy_rule;
        switch($coupon_library->policy) {
            case CashCouponPolicy::class:
                $threshold = $policy_rule['enough'] == 0 ? '无门槛' : '订单满' .$policy_rule['enough']. '元使用';
                $content = $policy_rule['cut'] .'元';
                break;
            case FeeCouponPolicy::class:
                $threshold = '无门槛';
                $cup_type = $policy_rule['cup_type'];
                $valen = $policy_rule['valen'];
                $typeArr = $this->proTypeName($coupon, $cup_type);
                if ($valen != '') {
                    $content = $typeArr[0].'-'.$typeArr[1].'('.self::VALEN[$valen].')';
                } else {
                    $content = $typeArr[0].'-'.$typeArr[1];
                }
                break;
            case BuyFeeCouponPolicy::class:
                $threshold = '无门槛';
                $buy = $policy_rule['buy'];
                $fee = $policy_rule['fee'];
                $valen = $policy_rule['valen'];
                $content = '买'.$buy.'赠'.$fee.'('.self::VALEN[$valen].')';
                break;
            case DiscountCouponPolicy::class:
                $threshold = '无门槛';
                $cup_type = $policy_rule['cup_type'];
                $valen = $policy_rule['valen'];
                $discount = $policy_rule['discount'];
                $content = '';
                $typeArr = $this->proTypeName($coupon, $cup_type, 1);
                if ($valen != '') {
                    $content = $typeArr[0].'-'.$typeArr[1].'-'.$discount.'折'.'('.self::VALEN[$valen].')';
                } else {
                    $content = $typeArr[0].'-'.$typeArr[1].'-'.$discount.'折';
                }
                break;
            case QueueCouponPolicy::class:
                $threshold = '无门槛';
                $content = '优先制作';
                break;
            default:
                $threshold = '';
                $content = '';
            break;
        }
        $dataArr = array_merge($dataArr, [
            'threshold' => $threshold,
            'content' => $content
        ]);
        return $dataArr;
    }
    
    /**
     * 喜茶券状态文字展示
     * @param type $status
     */
    public function statusText($status) {
        switch($status) {
            case 0:
                return '未领取';
            case 1:
                return '已领取';
            case 2:
                return '已使用';
            case 3:
                return '已过期';
            default:
                return '';
        }
    }
    
    /**
     * 获取门店饮品或类别
     * @param type $res
     * @return type
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
    
    /**
     * 获取使用场景
     * @param type $use_limit
     * @return string
     */
    public function useLimit($use_limit)
    {
        switch ($use_limit) {
            case Coupon::USELIMIT['self']:
                return '仅限自取';
            case Coupon::USELIMIT['takeout']:
                return '仅限外卖';
            default:
                return '全部可用';
        }
    }
    
    /**
     * 
     * 返回对象
     * $cup_type杯型(0, 1, 2, 3, 4)
     * $type类型（赠饮或折扣）
     * @param type $coupon
     * @param type $cup_type
     * @param type $type
     * @return string
     */
    public function proTypeName ($coupon, $cup_type, $type = 0) {
        switch($cup_type) {
            case 0:
                $text = !$type ? '赠送任一茶饮分类' : '订单总金额折扣';
                $catepro_name = !$type ? $this->getShopAndProduct($coupon->category) : '订单金额';
                break;
            case 1:
                $text = !$type ? '赠送指定商品' : '指定商品折扣';
                $catepro_name = $this->getShopAndProduct($coupon->product);
                break;
            case 2:
                $text = !$type ? '赠送' : '折扣';
                $catepro_name = '外卖运费';
                break;
            case 3:
                $text = !$type ? '赠送指定加料' : '指定加料折扣';
                $catepro_name = $this->getShopAndProduct($coupon->material);
                break;
            case 4:
                $text = $type ? '' : '指定分类折扣';
                $catepro_name = $this->getShopAndProduct($coupon->category);
            default:
                $text = '';
                $catepro_name = '';
                break;
        }
        return [$text, $catepro_name];
    }
}
