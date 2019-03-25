<?php

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use App\Models\Product;
use App\Policies\CouponLibrary\FeeCouponPolicy;
use App\Policies\CouponLibrary\BuyFeeCouponPolicy;
use App\Policies\CouponLibrary\CashCouponPolicy;
use App\Policies\CouponLibrary\QueueCouponPolicy;

class VkaCouponsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::beginTransaction();
        try {
//            $this->createCashCoupon();
//            $this->createFeeCoupon();
//            $this->createBuyFeeCoupon();
            $this->createQueueCoupon();
            DB::commit();
        } catch(\Exception $exception) {
            \Log::info('CHANGE_ERROR', [$exception]);
            echo 'vka喜茶券模板创建失败！请重试';
            \DB::rollBack();
        }
    }
    
    /**
     * 创建赠饮券
     */
    private function createCashCoupon()
    {
        $couponArr['policy'] = CashCouponPolicy::class;
        $rule = [
            'cut' => 1,
            'enough' => 0
        ];
        $couponArr['rule'] = $rule;
        if (!Coupon::where('flag', Coupon::FLAG['spring_game_cash'])->first()) {
            $couponArr['name'] = '春节游戏现金券';
            $this->createCoupon($couponArr, Coupon::FLAG['spring_game_cash']);
        }
    }
    
    /**
     * 创建赠饮券
     */
    private function createFeeCoupon()
    {
        $couponArr['policy'] = FeeCouponPolicy::class;
        $rule = [
            'cup' => 1,
            'valen' => 1,
            'cup_type' => 0
        ];
        $couponArr['rule'] = $rule;
        if (!Coupon::where('flag', Coupon::FLAG['vka_fee'])->first()) {
            $couponArr['name'] = '星球移民赠饮券';
            $this->createCoupon($couponArr, Coupon::FLAG['vka_fee'], 1);
        }
        if (!Coupon::where('flag', Coupon::FLAG['spring_game_fee'])->first()) {
            $couponArr['name'] = '春节游戏赠饮券';
            $this->createCoupon($couponArr, Coupon::FLAG['spring_game_fee'], 1);
        }
    }
    
    /**
     * 创建买赠券
     */
    private function createBuyFeeCoupon()
    {
        $couponArr['policy'] = BuyFeeCouponPolicy::class;
        $rule = [
            'buy' => 1,
            'fee' => 1,
            'valen' => 1
        ];
        $couponArr['rule'] = $rule;
        if (!Coupon::where('flag', Coupon::FLAG['vka_buyfee'])->first()) {
            $couponArr['name'] = '星球移民买一赠一券';
            $this->createCoupon($couponArr, Coupon::FLAG['vka_buyfee'], 1);
        }
    }
    
    /**
     * 创建优先券
     */
    private function createQueueCoupon()
    {
        $couponArr['policy'] = QueueCouponPolicy::class;
         $rule = [
            'share' => 1,
            'clsarr' => ['0', '1', '2', '3']
        ];
        $couponArr['rule'] = $rule;
        
        if (!Coupon::where('flag', Coupon::FLAG['hey_tea_queue'])->first()) {
            $couponArr['name'] = '伙伴专享-优先制作券';
            $this->createCoupon($couponArr, Coupon::FLAG['hey_tea_queue']);
        }
    }
    
    /**
     * 创建优惠券模板
     */
    private function createCoupon($couponArr, $flag, $category_limit = null)
    {
        $coupon = Coupon::create([
            'no' => create_no('TN'),
            'name' => $couponArr['name'],
            'policy' => $couponArr['policy'],
            'policy_rule' => $couponArr['rule'],
            'period_type' => Coupon::PERIODTYPE['relative'],
            'period_day' => 1,    //默认一个月后过期
            'unit_time' => Coupon::TIMEUNIT['month'],  // 默认以月为单位
            'use_limit' => Coupon::USELIMIT['all'],
            'count' => 1,
            'flag' => $flag,
            'admin_name' => '系统自动发券',
            'category_limit' => $category_limit ? 1 : 0
        ]);
        if ($category_limit) {
            $ids = [1, 3, 4, 5, 6, 7, 11, 12];
            $coupon->category()->sync($ids);
        }
    }
}
