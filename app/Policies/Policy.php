<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/15
 * Time: 14:01
 */

namespace App\Policies;


class Policy
{
    protected $rules = [];

    /**
     * 验证优惠券
     */
    public function verifyCoupon($policy_rule)
    {
        if (count($policy_rule) > 0) {
            return \Validator::make($policy_rule, $this->rules);
        }
        return null;
    }

    /**
     * 验证items数据格式
     * [
     * {
     * "product_id":1,
     * "price":10,
     * "quantity":1
     * },
     * {
     * "product_id":2,
     * "price":20,
     * "quantity":2
     * },
     * {
     * "product_id":3,
     * "price":30,
     * "quantity":3
     * }
     * ]
     * @param $items
     * @return bool
     */
    public static function verifyItems($items)
    {
        foreach ($items as $item) {
            if (empty($item['product_id'])) return false;
            if (empty($item['quantity'])) return false;
            if (empty($item['price'])) return false;
        }
        return true;
    }
}