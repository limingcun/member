<?php

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use App\Models\Product;
use App\Policies\CouponLibrary\FeeCouponPolicy;
use App\Policies\CouponLibrary\BuyFeeCouponPolicy;
use App\Policies\CouponLibrary\QueueCouponPolicy;

class PackageCouponsTableSeeder extends Seeder
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
            $this->createOneFeeLimitCoupon();
            $this->createOneQueueCoupon();
            $this->createOneFeeFreshCoupon();
            DB::commit();
        } catch(\Exception $exception) {
            \Log::info('CREATE_PACKAGE_ERROR', [$exception]);
            echo '券包模板创建失败！请重试';
            \DB::rollBack();
        }
    }
    
    /**
     * 创建赠饮券(当季限定)
     */
    private function createOneFeeLimitCoupon()
    {
        $coupon = Coupon::where('flag', Coupon::FLAG['one_fee_limit'])->first();
        if ($coupon) {
            return false;
        }
        $couponArr['policy'] = FeeCouponPolicy::class;
        $couponArr['name'] = '当季限定系列任1杯';
        $rule = [
            'cup' => 1,
            'valen' => 0,
            'cup_type' => 0
        ];
        $couponArr['rule'] = $rule;
        $couponArr['category_ids'] = [12];
        $this->createCoupon($couponArr, Coupon::FLAG['one_fee_limit'], 1, 1);
    }
    
    /**
     * 创建优先券
     */
    private function createOneQueueCoupon()
    {
        $coupon = Coupon::where('flag', Coupon::FLAG['one_queue'])->first();
        if ($coupon) {
            return false;
        }
        $couponArr['policy'] = QueueCouponPolicy::class;
        $couponArr['name'] = '提前制作券1张';
        $rule = [
            'share' => 1,
            'clsarr' => ['0', '1', '2', '3']
        ];
        $couponArr['rule'] = $rule;
        $this->createCoupon($couponArr, Coupon::FLAG['one_queue']);
    }
    
    /**
     * 创建赠饮券(喜茶鲜食)
     */
    private function createOneFeeFreshCoupon()
    {
        $coupon = Coupon::where('flag', Coupon::FLAG['one_fee_flesh'])->first();
        if ($coupon) {
            return false;
        }
        $couponArr['policy'] = FeeCouponPolicy::class;
        $couponArr['name'] = '任一茶饮-喜茶鲜食';
        $rule = [
            'cup' => 1,
            'valen' => 0,
            'cup_type' => 0
        ];
        $couponArr['rule'] = $rule;
        $couponArr['category_ids'] = [20];
        $this->createCoupon($couponArr, Coupon::FLAG['one_fee_flesh'], 1, 1);
    }

    

    /**
     * 创建优惠券模板
     */
    private function createCoupon($couponArr, $flag, $category_limit = null, $use_limit = 0)
    {
        $coupon = Coupon::create([
            'no' => create_no('TN'),
            'name' => $couponArr['name'],
            'policy' => $couponArr['policy'],
            'policy_rule' => $couponArr['rule'],
            'period_type' => Coupon::PERIODTYPE['relative'],
            'period_day' => 1,    //默认一个月后过期
            'unit_time' => Coupon::TIMEUNIT['month'],  // 默认以月为单位
            'use_limit' => $use_limit,
            'count' => 1,
            'flag' => $flag,
            'admin_name' => '系统自动发券',
            'category_limit' => $category_limit ? 1 : 0
        ]);
        if ($category_limit) {
            $ids = $couponArr['category_ids'];
            $coupon->category()->sync($ids);
        }
    }
}
