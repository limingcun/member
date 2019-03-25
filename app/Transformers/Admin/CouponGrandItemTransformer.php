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

class CouponGrandItemTransformer extends TransformerAbstract
{
    const POLICY = [
        CashCouponPolicy::class,
        FeeCouponPolicy::class,
        BuyFeeCouponPolicy::class,
        DiscountCouponPolicy::class,
        QueueCouponPolicy::class
    ];
    /**
     *
     * 优惠券数据获取和转化
     * @return array
     */
    public function transform(CouponGrand $coupon_grand)
    {
        $coupon = $coupon_grand->coupon;
        $grand_all = $coupon_grand->library->count();
        $grand_num = $coupon_grand->grandNum->count();
        $use_num = $coupon_grand->useNum->count();
        $unuse_num = $coupon_grand->unuseNum->count();
        $order_count = $coupon_grand->orderCoupon->count();
        $order_discount_fee = $coupon_grand->orderCoupon->sum('discount_fee');
        switch ($coupon_grand->scence) {
            case CouponGrand::SCENCE['line']:
                $scence = '小程序';
                break;
            case CouponGrand::SCENCE['change']:
                $scence = '兑换码';
                break;
            case CouponGrand::SCENCE['qrcode']:
                $scence = '二维码';
                break;
            default:
                $scence = '未知';
                break;
        }
        $dataArr = [
            'id' => $coupon_grand->id,
            'name' => $coupon_grand->name,
            'grand_no' => $coupon_grand->no,
            'coupon_no' => $coupon->no ?? '',
            'coupon_name' => $coupon->name ?? '',
            'period_time' => !$coupon->period_type ? Carbon::parse($coupon->period_start)->format('Y-m-d') . '至' . Carbon::parse($coupon->period_end)->format('Y-m-d') :
                '自发放之日起' . $coupon->period_day . Coupon::DATE[$coupon->unit_time] .'内有效',
            'scence' => $scence,
            'count' => $coupon_grand->count,
            'range_type' => $this->getRange($coupon_grand),
            'created_at' => (string) $coupon_grand->grand_time,
            'created_man' => $coupon_grand->admin->name ?? '',
            'shop_limit' => $coupon->shop_limit ? $this->getShopAndProduct($coupon->shop) : 0,
            'product_limit' => $coupon->product_limit ? $this->getShopAndProduct($coupon->product) : 0,
            'use_limit' => $coupon->use_limit ? $this->useLimit($coupon->use_limit) : '全部可用',
            'grand_all' => $grand_all ?? 0,
            'grand_num' => $grand_num ?? 0,
            'use_num' => $use_num ?? 0,
            'pick_num' => $grand_num ?? 0,
            'unuse_num' => $unuse_num ?? 0,
            'order_count' => $order_count ?? 0,
            'order_discount_money' => round($order_discount_fee, 2) ?? 0,
            'tatal_fee' => $coupon->order()->sum('total_fee')
        ];
        $policy = $coupon->policy;
        $policy_rule = $coupon->policy_rule;
        switch($policy) {
            case self::POLICY[0]:
                if (gettype($policy_rule) == 'string') {
                    $policy_rule = json_decode($policy_rule, true);
                }
                $dataArr = array_merge($dataArr, [
                    'type' => '现金券',
                    'cut' => $policy_rule['cut'].'元',
                    'enough' => $policy_rule['enough'] == 0 ? '无门槛' : '满'.$policy_rule['enough'].'元使用',
                    'all_money' => round($grand_all * $policy_rule['cut'], 2)
                ]);
                break;
            case self::POLICY[1]:
                $cup_type = $policy_rule['cup_type'];
                $catepro_name = $this->proTypeName($coupon, $cup_type);
                $dataArr = array_merge($dataArr, [
                    'type' => '赠饮券',
                    'cut_type' => $cup_type,
                    'catepro_name' => $catepro_name,
                    'valen' => $policy_rule['valen'],
                    'all_money' => '--'
                ]);
                break;
            case self::POLICY[2]:
                $dataArr = array_merge($dataArr, [
                    'type' => '买赠券',
                    'buy' => $policy_rule['buy'],
                    'fee' => $policy_rule['fee'],
                    'valen' => $policy_rule['valen'],
                    'all_money' => '--'
                ]);
                break;
            case self::POLICY[3]:
                $cup_type = $policy_rule['cup_type'];
                $catepro_name = $this->proTypeName($coupon, $cup_type, 1);
                $dataArr = array_merge($dataArr, [
                    'type' => '折扣券',
                    'catepro_name' => $catepro_name,
                    'discount' => $policy_rule['discount'],
                    'cup_type' => $policy_rule['cup_type'],
                    'valen' => $policy_rule['valen'],
                    'all_money' => '--'
                ]);
                break;
            default:
                $dataArr = array_merge($dataArr, [
                    'type' => '优先券',
                    'share' => $policy_rule['share'],
                    'clsarr' => $policy_rule['clsarr'],
                    'all_money' => '--'
                ]);
                break;
        }
        return $dataArr;   
    }

    /*
     * 判断用户类型
     */
    public function getRange($grand)
    {
        switch ($grand->scence) {
            case CouponGrand::SCENCE['line']:
                return !$grand->range_type ? '全部用户' : '指定用户';
            case CouponGrand::SCENCE['change']:
            case CouponGrand::SCENCE['qrcode']:
                return $grand->range_msg;
            default:
                return;
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
     * 获取使用场景
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
    
    /*
     * 返回对象
     * $cup_type杯型(0, 1, 2, 3, 4)
     * $type类型（赠饮或折扣）
     * @return返回对象类型名称
     */
    public function proTypeName ($coupon, $cup_type, $type = 0) {
        switch($cup_type) {
            case 0:
                $catepro_name = !$type ? $this->getShopAndProduct($coupon->category) : '订单金额';
                break;
            case 1:
                $catepro_name = $this->getShopAndProduct($coupon->product);
                break;
            case 2:
                $catepro_name = '外卖运费';
                break;
            case 3:
                $catepro_name = $this->getShopAndProduct($coupon->material);
                break;
            default:
                $catepro_name = '';
                break;
        }
        return $catepro_name;
    }
}
