<?php


namespace App\Transformers\Admin;



use App\Models\CouponLibrary;

class MemberCouponTransformer extends BaseCouponTransformer
{
    public function transform(CouponLibrary $coupon_library)
    {
        /*
         * 券编号  no
         * 名称 name
         * 领券人  user.name
         * 优惠内容 cut
         * 门槛   enough
         * 券类型  policy
         * 券状态  status
         * 有效期  period_date
         * 使用条件 use_limit
         * 领取时间 created_at
         * 创建人  created_man
         * 使用时间 used_at
         * 对应订单号 order_id
         * */
        $coupon = $coupon_library->coupon()->firstOrFail();
        $arr = [
            'no' => $coupon->no,
            'name' => $coupon_library->name,
            'user' => $coupon_library->user()->pluck('name')[0],
            'status' => $this->getStatus($coupon_library->status),
            'period_date' => $this->getPeriod($coupon_library, $coupon),
            'shop_limit' => $coupon->shop_limit ? $this->getShopAndProduct($coupon->shop) : '',
            'product_limit' => $coupon->product_limit ? $this->getShopAndProduct($coupon->product) : '',
            'use_limit' => $coupon->use_limit ? $this->getuseLimit($coupon->use_limit) : '全部可用',
            'create_at' => (string)$coupon_library->created_at,
            'created_man' => $coupon->admin_name,
            'used_at' => $coupon_library->used_at,
            'order_id' => $coupon_library->order_id
        ];
        $policy_rule = $this->getCouponPolicy($coupon_library->policy, $coupon_library->policy_rule);
        $arr['policy'] = $policy_rule['type'];
        $arr['cut'] = $policy_rule['cut'];
        $arr['enough'] = $policy_rule['enough'];
        return $arr;
    }
}
