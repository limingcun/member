<?php

namespace App\Transformers\Admin;

use App\Models\MallOrder;
use App\Models\MallOrderCoupon;
use App\Models\MallOrderEntity;
use App\Models\MallProduct;
use App\Policies\CouponLibrary\BuyFeeCouponPolicy;
use App\Policies\CouponLibrary\DiscountCouponPolicy;
use App\Policies\CouponLibrary\FeeCouponPolicy;
use App\Policies\CouponLibrary\QueueCouponPolicy;
use App\Services\JuheExp;
use App\Services\KDNiao;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;
use App\Models\Coupon;
use App\Policies\CouponLibrary\CashCouponPolicy;

class MallOrderItemTransformer extends TransformerAbstract
{
    /**
     *
     * 积分商城订单数据获取和转化
     * @return array
     */
    public function transform(MallOrder $mall_order)
    {
        $item = $mall_order->item;
        $product = $item->product;
        $source = $item->source;
        $data = [
            'id' => $mall_order->id,
            'no' => $mall_order->no,
            'exchange_time' => $mall_order->exchange_time,
            'name' => $mall_order->item->name,
            'user_id' => $mall_order->user_id,
            'user_name' => $mall_order->user->name,
            'user_phone' => $mall_order->user->phone,
            'score' => $mall_order->score,
            'status_text' => $this->getStatusText($mall_order->status),
            'status' => $mall_order->status,
            'no_code' => $product->no_code,
            'mall_type' => $mall_order->mall_type,
            'image_url' => $product->images[0]->path ?? '',
            'http_url' => env('QINIU_URL')
        ];
        if (MallOrderCoupon::class == $item->source_type) {
            $policy_rule = $source->policy_rule;
            if (CashCouponPolicy::class == $source->policy) {
                $type = '现金券';
            } elseif (BuyFeeCouponPolicy::class == $source->policy) {
                $type = '买赠券';
            } elseif (DiscountCouponPolicy::class == $source->policy) {
                $type = '折扣券';
            } elseif (FeeCouponPolicy::class == $source->policy) {
                $type = '赠饮券';
            } elseif (QueueCouponPolicy::class == $source->policy) {
                $type = '优先券';
            } else {
                $type = '其他';
            }
            $data = array_merge($data, [
                'mall_coupon_id' => $source->code_id, //优惠券编码
                'type' => $type,
                'period_time' => $source->period_start->format('Y-m-d') . '至' . $source->period_end->format('Y-m-d'),
                'use_limit' => $this->useLimit($source->use_limit),
                'coupon_no' => $source->no
            ]);
        }
        if (MallOrderEntity::class == $item->source_type) {
            if (!$trace = $mall_order->express->trace) {
                if ($mall_order->express->shipper_code | $mall_order->express->no) {
                    $JuheExp = new JuheExp();
                    $delivery = $JuheExp->query($mall_order->express->shipper_code, $mall_order->express->no);
                    $trace = is_array($delivery) ? $delivery['list'] : [];
                } else {
                    $trace = [];
                }
            }
            $data = array_merge($data, [
                'express' => $mall_order->express,
                'specifications' => $source->specifications,
                'traces' => $trace,
            ]);
        }
        return $data;
    }

    public function useLimit($use_limit)
    {
        switch ($use_limit) {
            case Coupon::USELIMIT['all']:
                return '全部可用';
            case Coupon::USELIMIT['self']:
                return '仅限自取';
            case Coupon::USELIMIT['takeout']:
                return '仅限外卖';
            default:
                return '全部可用';
        }
    }

    /*
     * 返回规则各属性
     */
    public function proper($policy_rule, $field)
    {
        if (gettype($policy_rule) == 'string') {
            $policy_rule = json_decode($policy_rule, true);
        }
        return $policy_rule[$field] ?? '';
    }

    /*
     * 前端状态显示数据
     */
    public function getStatusText($status)
    {
        switch ($status) {
            case MallOrder::STATUS['success']:
                return '兑换成功';
            case MallOrder::STATUS['fail']:
                return '兑换失败';
            case MallOrder::STATUS['wait_dispatch']:
                return '待发货';
            case MallOrder::STATUS['dispatching']:
                return '已发货';
            case MallOrder::STATUS['finish']:
                return '已完成';
            case MallOrder::STATUS['refund']:
                return '已退单';
            default:
                return null;
        }
    }
}
