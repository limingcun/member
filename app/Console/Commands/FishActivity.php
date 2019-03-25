<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coupon;
use App\Models\CouponLibrary;
use App\Models\Member;
use App\Policies\CouponLibrary\CashCouponPolicy;
use App\Policies\CouponLibrary\FeeCouponPolicy;
use Carbon\Carbon;
use IQuery;
use Log;
use DB;

class FishActivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fish:activity {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '锦鲤活动';

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
        $user_ids = $this->argument('user_id');  //获取立即发券的id串
        $user_ids = explode(',', $user_ids);
        $rule['cup'] = 1;
        $rule['valen'] = 1;
        $rule['cup_type'] = 0;
        $name = '喜茶赠饮券';
        DB::beginTransaction();
        try {
            //新建模板
            $coupon = Coupon::create([
                'no' => create_no('TN'),
                'name' => $name,
                'policy' => FeeCouponPolicy::class,
                'policy_rule' => json_encode($rule),
                'period_type' => Coupon::PERIODTYPE['fixed'],
                'period_start' => Carbon::today(),
                'period_end' => Carbon::today(),
                'use_limit' => 0,
                'count' => 0,
                'flag' => Coupon::FLAG['active_gold_pig'],
                'admin_name' => '系统自动发券',
                'category_limit' => 1
            ]);
            $category_ids = [1, 3, 4, 5, 6, 7, 11, 12];
            $coupon->category()->sync($category_ids);
            foreach($user_ids as $k => $user_id) {
                $this->createLibrary($k, $user_id, $coupon);
            }
            Member::whereIn('user_id', $user_ids)->update(['new_coupon_tab' => Member::NEWTAB['new']]);
            DB::commit();
            Log::info('FISH_ACTIVE_SUCCESS', ['SUCCESS']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info('FISH_ACTIVE_ERROR', [$e]);
        }
    }
    
    public function createLibrary($n, $user_id, $coupon) {
        if ($n == 0) {
            $number = 30;
        } else if ($n == 1) {
            $number = 60;
        } else {
            $number = 365;
        }
        $couponLibrary = new CouponLibrary;
        for($i = 0; $i < $number; $i++) {
            $couponArr[] = [
                'code_id' => $coupon->no .IQuery::strPad($i + 1),
                'user_id' => $user_id,
                'name' => $coupon->name,
                'coupon_id' => $coupon->id,
                'source_id' => $coupon->id,
                'source_type' => Coupon::class,
                'policy' => FeeCouponPolicy::class,
                'policy_rule' => $coupon->policy_rule,
                'period_start' => Carbon::today()->addDays($i)->startOfDay(),  //period_type为固定时间和相对时间
                'period_end' => Carbon::today()->addDays($i)->startOfDay(),
                'use_limit' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'status' => CouponLibrary::STATUS['surplus'],
                'tab' => CouponLibrary::NEWTAB['new']
            ];
        }
        $couponLibrary->maxInsert($couponArr);
    }
}
