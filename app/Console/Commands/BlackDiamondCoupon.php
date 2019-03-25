<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use App\Models\CouponLibrary;
use App\Models\Member;
use App\Utils\IQuery;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BlackDiamondCoupon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coupon:mend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '黑钻会员补券';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Member::where('star_level_id', '6')->select(['id', 'user_id', 'star_time'])->chunk(200, function ($members) {
            $coupon = Coupon::where('flag', Coupon::FLAG['fee_star_anniversary'])->select('id')->first();
            foreach ($members as $member) {
                // 查找发券次数
               $count = CouponLibrary::where('user_id', $member->user_id)->where('coupon_id', $coupon->id)
                   ->where('period_start', '>=', Carbon::today())->select('id')->count();
               if ($count < 2) {
                   // 不够两张就补发
                   $num = 2 - $count;
                   $star_time = $member->star_time;
                   if ($star_time) {
                       if (strtotime($star_time) < strtotime(Carbon::today()->endOfDay())) {
                           $this->createCoupon('fee_star_anniversary', $member->user_id, $num,
                               Carbon::createFromTimestamp(strtotime($star_time))->addYear(1)->endOfDay(),
                               Carbon::createFromTimestamp(strtotime($star_time))->addYear(1)->startOfDay());
                       } else {
                           $this->createCoupon('fee_star_anniversary', $member->user_id, $num,
                               Carbon::createFromTimestamp(strtotime($star_time))->endOfDay(),
                               Carbon::createFromTimestamp(strtotime($star_time))->startOfDay());
                       }
                   }
               }
            }
        });
    }

    public function createCoupon($tpl, $user_id, $amount, $period_end = null, $period_start = null)
    {
        // 取最新的券模板
        $coupon = Coupon::where('flag', Coupon::FLAG[$tpl])
            ->orderBy('updated_at', 'desc')->first();
        return $this->createCouponLibrarys($user_id, $coupon, $amount, $period_end, $period_start);
    }

    public function createCouponLibrarys($user_id, Coupon $coupon, $amount, $period_end = null, $period_start = null)
    {
        for ($i = 0; $i < $amount; $i++) {
            $library = CouponLibrary::create([
                'name' => $coupon->name,
                'user_id' => $user_id,
                'order_id' => 0,
                'coupon_id' => $coupon->id,
                'policy' => $coupon->policy,
                'policy_rule' => $coupon->policy_rule,
                'source_id' => $coupon->id,
                'source_type' => Coupon::class,
                'period_start' => $period_start ? $period_start : \Carbon\Carbon::today(),
                'period_end' => $period_end ? $period_end : Coupon::getTimePeriod($coupon), // 默认以优惠券模板为准
                'status' => CouponLibrary::STATUS['surplus'],
                'tab' => CouponLibrary::NEWTAB['scan'],
                'code_id' => $coupon->no . @IQuery::strPad($i + 1),
                'use_limit' => $coupon->use_limit
            ]);
        }
        return $library;
    }
}
