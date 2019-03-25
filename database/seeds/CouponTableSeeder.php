<?php

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use App\Models\CouponLibrary;

class CouponTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    const NUM_TRANSFORM = [
        1 => '一',
        2 => '二',
        3 => '三',
        4 => '四',
        5 => '五',
        6 => '六',
    ];

    public function run()
    {
        /**
         * 创建会员升级优惠券模板
         * 发券规则
         * LV 1-4 //3张 满120减5券  2张 满6赠1券
         * LV 5-9 //3 110-10 2 5-1
         * LV 10-14 //3 110-15 3 4-1
         * LV 15-19 //3 100-15 3 3-1
         * LV 20-24 //3 100-20 3 3-1
         * LV 25-30 //3 100-25 3 2-1
        */
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
//            DB::table('coupons')->where('flag', '>', 9)->delete();
            foreach (Coupon::CASH_COUPON_TPL as $arr){
                $this->createCashCoupon($arr[0],"满{$arr[1]}减{$arr[2]}元", $arr[1], $arr[2], 1);
            }
            foreach (Coupon::BUY_FEE_COUPON_TPL as $arr){
                $this->createBuyFeeCoupon($arr[0], '买'.self::NUM_TRANSFORM[$arr[1]].'赠'.self::NUM_TRANSFORM[$arr[2]], $arr[1], $arr[2], 1);
            }
            // 星球开通纪念日 赠饮券模板
            $this->createFeeCoupon('fee_star_anniversary', '星球会员开通纪念日赠饮券', 1, CouponLibrary::CUPTYPE['category']);
            // 星球会员升级瞬间礼包 赠饮券
            $this->createFeeCoupon('fee_star_update', '星球升级赠饮券', 1, CouponLibrary::CUPTYPE['category']);
            // 星球会员 钻石 满20单 兑换赠饮券
            $this->createFeeCoupon('fee_star_20', '满单赠饮券', 1, CouponLibrary::CUPTYPE['category']);
            // 星球会员 黑金 满10单 兑换赠饮券
            $this->createFeeCoupon('fee_star_10', '满单赠饮券', 1, CouponLibrary::CUPTYPE['category']);
            // 星球会员 黑钻 满5单 兑换赠饮券
            $this->createFeeCoupon('fee_star_5', '满单赠饮券', 1, CouponLibrary::CUPTYPE['category']);
            // 星球会员每月福利 折扣券模板
            $this->createDiscountCoupon('discount_star_month', '限定饮品9折券', 9, CouponLibrary::CUPTYPE['discount_category'], 1);
            // 星球会员生日好礼赠饮券
            $this->createFeeCoupon('fee_star_birthday', '会员尊享-生日赠饮券', 1, CouponLibrary::CUPTYPE['category']);
            // 星球会员5.12会员日赠饮券
            $this->createFeeCoupon('fee_star_prime_day', '5.12会员日赠饮券', 1, CouponLibrary::CUPTYPE['category']);
            // 首充奖励 10元现金券
            $this->createCashCoupon('cash_star_first', '10元代金券', 0, 10, 1);
            // 星球会员每月福利 优先券模板
            $this->createQueueCoupon('queue_star_month', '每月优先券', 1);
            // 星球会员购卡奖励 买二送一
            $this->createBuyFeeCoupon('buy_fee_card_2-1', '买二赠一', 2, 1, 1);
            // 星球会员购卡奖励 买一送一
            $this->createBuyFeeCoupon('buy_fee_card_1-1', '买一赠一', 1, 1, 1);
            // 星球会员购卡奖励 免运费券
            $this->createFeeCoupon('fee_card_take_fee', '免运费券', 1, CouponLibrary::CUPTYPE['take']);
            // 星球会员购卡奖励 指定饮品立减券（折扣券）
            $this->createDiscountCoupon('discount_card', '限定饮品9折券', 9, CouponLibrary::CUPTYPE['discount_category'], 1);
            // 星球会员购卡奖励 优先券
            $this->createQueueCoupon('queue_card', '优先券', 1);

            // 喜茶员工福利
            $this->createDiscountCoupon('hey_tea_discount_5', '伙伴专享-单品五折券', 5, CouponLibrary::CUPTYPE['discount_category']);
            $this->createCashCoupon('hey_tea_cash_25', '伙伴专享-25元代金券', 0, 25, 1);
            $this->createFeeCoupon('hey_tea_fee_day', '伙伴专享-喜茶司庆赠饮券', 1, CouponLibrary::CUPTYPE['category']);
            $this->createFeeCoupon('hey_tea_fee_join_day', '伙伴专享-入职赠饮券', 1, CouponLibrary::CUPTYPE['category']);
            $this->createFeeCoupon('hey_tea_fee_join_anniversary', '伙伴专享-入职周年赠饮券', 1, CouponLibrary::CUPTYPE['category']);
            $this->createFeeCoupon('hey_tea_fee_birthday', '伙伴专享-生日赠饮券', 1, CouponLibrary::CUPTYPE['category']);

            // 星球会员
            $this->createBuyFeeCoupon('invite_5_buy_fee', '买一赠一', 1, 1, 1, 0);
            $this->createBuyFeeCoupon('invite_10_buy_fee', '买一赠一', 1, 1, 1, 0);

            \Illuminate\Support\Facades\DB::commit();
            \IQuery::redisDelete('flag_coupon_id');
        } catch (Exception $exception) {
            \Illuminate\Support\Facades\DB::rollBack();
            echo '优惠券模板创建失败！请重试';
        }
    }

    /**
     * 创建满减券
     */
    private function createCashCoupon($type, $name, $enough, $cut, $amount=1)
    {
        $coupon['policy'] = \App\Policies\CouponLibrary\CashCouponPolicy::class;
        $coupon['name'] = $name;
        $coupon['count'] = $amount;
        $coupon['type'] = $type;    //券模板标识
        $rule = [
            "cut" => "$cut",
            "enough" => "$enough"
        ];
        $coupon['rule'] = $rule;
        $this->createCoupon($coupon);
    }

    /**
     * 创建买赠券
     */
    private function createBuyFeeCoupon($type, $name, $buy, $fee, $amount, $valen=1)
    {
        $coupon['policy'] = \App\Policies\CouponLibrary\BuyFeeCouponPolicy::class;
        $coupon['name'] = $name;
        $coupon['count'] = $amount;
        $coupon['type'] = $type;
        $rule = [
            'buy' => "$buy",
            'fee' => "$fee",
            'valen' => $valen ? "$valen" : 1,
        ];
        $coupon['rule'] = $rule;
        $coupon['category_limit'] = 1;
        $coupon = $this->createCoupon($coupon);
        // 目前有分类中没有字段区分什么是饮品什么是面包
        // 跟运营产品沟通后 赠饮券目前只支持这几个分类 这里是线上的id
        $ids = [1, 3, 4, 5, 6, 7, 11, 12];
        $coupon->category()->syncWithoutDetaching($ids);
    }

    /**
     * 创建赠饮券
     */
    private function createFeeCoupon($type, $name, $cup, $cup_type, $amount=1, $valen=1)
    {
        $coupon['policy'] = \App\Policies\CouponLibrary\FeeCouponPolicy::class;
        $coupon['name'] = $name;
        $coupon['count'] = $amount;
        $coupon['type'] = $type;
        $rule = [
            'cup' => "$cup",
            'valen' => $valen ? "$valen" : null,
            'cup_type' => $cup_type
        ];
        $coupon['rule'] = $rule;
        if ($cup_type == CouponLibrary::CUPTYPE['take']) {
            $coupon['use_limit'] = Coupon::USELIMIT['takeout'];
        } elseif ($cup_type == CouponLibrary::CUPTYPE['category']) {
            $coupon['category_limit'] = 1;
        }
        $coupon = $this->createCoupon($coupon);
        if ($cup_type == CouponLibrary::CUPTYPE['category']) {
            // 目前分类中没有字段区分什么是饮品什么是面包
            // 跟运营产品沟通后 赠饮券目前只支持这几个分类 这里是线上的id
            $ids = [1, 3, 4, 5, 6, 7, 11, 12];
            $coupon->category()->syncWithoutDetaching($ids);
        }
    }

    /**
     * 创建折扣券
     */
    private function createDiscountCoupon($type, $name, $discount, $cup_type, $valen=0, $amount=1)
    {
        $coupon['policy'] = \App\Policies\CouponLibrary\DiscountCouponPolicy::class;
        $coupon['name'] = $name;
        $coupon['count'] = $amount;
        $coupon['type'] = $type;
        $rule = [
            'discount' => "$discount",
            'valen' => $valen,
            'cup_type' => $cup_type
        ];
        $coupon['rule'] = $rule;
        $coupon['category_limit'] = 1;
        $coupon = $this->createCoupon($coupon);
        // 目前创建的折扣券 只支持折扣新品分类下的商品  线上的当季限定id为 12
        $ids = [12];
        $coupon->category()->syncWithoutDetaching($ids);
    }

    /**
     * 创建优先券
     */
    private function createQueueCoupon($type, $name, $share, $clsarr=["0", "1", "2", "3"], $amount=1)
    {
        $coupon['policy'] = \App\Policies\CouponLibrary\QueueCouponPolicy::class;
        $coupon['name'] = $name;
        $coupon['count'] = $amount;
        $coupon['type'] = $type;
        $rule = [
            'share' => $share,
            'clsarr' => $clsarr,
        ];
        $coupon['rule'] = $rule;
        $this->createCoupon($coupon);
    }

    /**
     * 创建优惠券模板
     */
    private function createCoupon($arr)
    {
        $coupon = Coupon::where('flag', Coupon::FLAG[$arr['type']])->first();
        if ($coupon) {
            $coupon->update([
                'name' => $arr['name'],
                'policy' => $arr['policy'],
                'policy_rule' => $arr['rule'],
                'use_limit' => isset($arr['use_limit']) ? $arr['use_limit'] : Coupon::USELIMIT['all'],
                'count' => $arr['count'],
                'flag' => Coupon::FLAG[$arr['type']],
                'category_limit' => $arr['category_limit'] ?? 0,
            ]);
        } else {
            $coupon = Coupon::create([
                'no' => create_no('TN'),
                'name' => $arr['name'],
                'policy' => $arr['policy'],
                'policy_rule' => $arr['rule'],
                'period_type' => Coupon::PERIODTYPE['relative'],
                'period_day' => 1,    // 默认一个月后过期
                'unit_time' => Coupon::TIMEUNIT['month'],  // 默认以月为单位
                'use_limit' => isset($arr['use_limit']) ? $arr['use_limit'] : Coupon::USELIMIT['all'],
                'count' => $arr['count'],
                'flag' => Coupon::FLAG[$arr['type']],
                'category_limit' => $arr['category_limit'] ?? 0,
                'admin_name' => '系统自动发券',
            ]);
        }

        return $coupon;
    }
}
