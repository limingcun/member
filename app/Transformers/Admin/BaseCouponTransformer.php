<?php
/**
 * Created by PhpStorm.
 * User: lijx
 * Date: 2018/9/28/028
 * Time: 14:47
 */

namespace App\Transformers\Admin;


use App\Models\Category;
use App\Models\Coupon;
use App\Models\CouponLibrary;
use App\Models\Product;
use App\Policies\CouponLibrary\BuyFeeCouponPolicy;
use App\Policies\CouponLibrary\CashCouponPolicy;
use App\Policies\CouponLibrary\DiscountCouponPolicy;
use App\Policies\CouponLibrary\FeeCouponPolicy;
use App\Policies\CouponLibrary\QueueCouponPolicy;
use League\Fractal\TransformerAbstract;

class BaseCouponTransformer extends TransformerAbstract
{
    const POLICY = [
        CashCouponPolicy::class => '现金券',
        FeeCouponPolicy::class => '赠饮券',
        BuyFeeCouponPolicy::class => '买赠券',
        DiscountCouponPolicy::class => '折扣券'
    ];

    const VALEN = [
        null => '',
        0 => '最高价',
        1 => '次高价',
        2 => '次低价',
        3 => '最低价',
    ];

    /**
     * 获取使用场景
     * @param $use_limit
     * @return string
     */
    public function getUseLimit($use_limit)
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
     * 获取优惠券的状态
     * @param $status
     * @return string
     */
    public function getStatus($status) {
        switch($status) {
            case CouponLibrary::STATUS['unpick']:
                return '未领取';
            case CouponLibrary::STATUS['surplus']:
                return '已领取';
            case CouponLibrary::STATUS['used']:
                return '已使用';
            case CouponLibrary::STATUS['period']:
                return '已过期';
            default:
                return '';
        }
    }

    /**
     * 获取门店和饮品
     * @param $res
     * @return string
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
     * 获取活动类型
     * @param $flag
     * @return string
     */
    public function getActive($flag)
    {
        switch ($flag) {
            case Coupon::FLAG['coupon']:
                return '手动发券（活动ID）';
            case Coupon::FLAG['mall']:
                return '积分商城（商品ID）';
            default:
                return '尚未绑定活动';
        }
    }

    /**
     * 获取优惠券的有效期
     * @param $coupon_library
     * @param $coupon
     * @return string
     */
    public function getPeriod($coupon_library, $coupon)
    {
        return !$coupon_library->period_type ? $coupon_library->period_start->format('Y-m-d H:i:s') . '至' . $coupon_library->period_end->format('Y-m-d H:i:s') :
            $coupon_library->grand->grand_time->format('Y-m-d H:i:s') . '至' . Coupon::getTimePeriod($coupon, $coupon->grand->grand_time);
    }

    /**
     * 获取优惠券内容
     * @param $policy
     * @param $policy_rule
     * @return bool|array
     */
    public function getCouponPolicy($policy, $policy_rule)
    {
        switch ($policy){
            case CashCouponPolicy::class:
                return $this->getCashCouponPolicy($policy_rule);
            case FeeCouponPolicy::class:
                return $this->getFeeCouponPolicy($policy_rule);
            case BuyFeeCouponPolicy::class:
                return $this->getBuyFeeCouponPolicy($policy_rule);
            case DiscountCouponPolicy::class:
                return $this->getDiscountCouponPolicy($policy_rule);
            case QueueCouponPolicy::class:
                return $this->getQueueCouponPolicy($policy_rule);
            default:
                // 异常券类型
                return false;
        }
    }

    /**
     * 现金券
     * @param $policy_rule
     * @return array
     */
    private function getCashCouponPolicy($policy_rule)
    {
        $arr['type'] = '现金券';
        $arr['cut'] = $policy_rule['cut'].'元';
        $arr['enough'] = $policy_rule['enough'] == 0 ? '无门槛' : '满'.$policy_rule['enough'].'元使用';
        return $arr;
    }

    /**
     * 赠饮券
     * @param $policy_rule
     * @return array
     */
    private function getFeeCouponPolicy($policy_rule)
    {
        $arr['type'] = '赠饮券';
        $arr['cup'] = $policy_rule['cup'].'杯';
        $arr['cup_type'] = $policy_rule['cup_type'].'杯';
        $arr['valen'] = self::VALEN[$policy_rule['valen']];
        $arr['enough'] = $policy_rule['enough'] == 0 ? '无门槛' : '满'.$policy_rule['enough'].'元使用';
        return $arr;
    }

    /**
     * 买赠券
     * @param $policy_rule
     * @return array
     */
    private function getBuyFeeCouponPolicy($policy_rule)
    {
        $arr['type'] = '买赠券';
        $arr['cup'] = $policy_rule['buy'].'杯';
        $arr['valen'] = self::VALEN[$policy_rule['valen']];
        $arr['cut'] = "买 {$policy_rule['buy']} 杯赠 {$arr['valen']} {$policy_rule['fee']} 杯";
        $arr['enough'] = '满' . $arr['cup'] . '可用';
        return $arr;
    }

    /**
     * 折扣券
     * @param $policy_rule
     * @return array
     */
    private function getDiscountCouponPolicy($policy_rule)
    {
        // 优惠内容： 订单中 指定商品 的最高价的一杯 xx折  门槛：无门槛？
        // 订单中 配送费 xx折    订单中 订单金额 xx 折     订单中 指定加料 xx折
        $cup_type = [
            0 => '订单价格',
            1 => '指定商品',
            2 => '配送费',
            3 => '指定加料',
        ];
        $arr['type'] = '折扣券';
        $arr['discount'] = $policy_rule['discount'] . '折';
        $arr['valen'] = self::VALEN[$policy_rule['valen']];
        $arr['cup_type'] = '订单中' . $cup_type[$policy_rule['cup_type']];
        $arr['enough'] = '无门槛'; // 待确认
        $arr['cut'] = $arr['cup_type'] . $arr['valen'] . $arr['discount'];
        return $arr;
    }

    /**
     * 优先券
     * @param $policy_rule
     * @return array
     */
    private function getQueueCouponPolicy($policy_rule)
    {
        $arr['type'] = '优先券';
        $arr['share'] = $policy_rule['share'];
        $arr['clsarr'] = $policy_rule['clsarr'];
        $arr['cut'] = "优先制作";
        $arr['enough'] = '无门槛';
        return $arr;
    }
}
