<?php

/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/11/28
 * Time: 下午16:39
 * desc: 积分经验值补录
 */
namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Member;
use App\Models\CouponLibrary;
use App\Models\Coupon;
use App\Models\User;
use Log;
use DB;
use IQuery;

class ShopWelfare extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:welfare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '喜茶门店员工福利';
    
    protected $redis_path = 'laravel:employee_welfare:';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $redis_path = $this->redis_path.'shop';
        if (!IQuery::redisExists($redis_path)) {
            return;
        }
        DB::beginTransaction();
        try {
            $res = IQuery::redisGet($redis_path);
            $arrs = array_splice($res, 0, 500);
            foreach($arrs as $k => $arr) {
                $index = $arr[0]; //序号
                $name = $arr[1];  //姓名
                $phone = $arr[2]; //电话
                $employ = $arr[3]; //入职时间
                $birthday = $arr[4]; //生日
                $user = User::where('phone', $phone)->first();
                if (!$user) {
                    continue;
                }
                //25元代金券(5张)
                $this->createLibrary($user, Coupon::FLAG['hey_tea_cash_25'], 5);
                //判断是否是这个月生日
                if (Carbon::today()->format('m') == Carbon::parse($birthday)->format('m')) {
                    $this->createLibrary($user, Coupon::FLAG['hey_tea_fee_birthday'], 1);
                }
                //判断是否是这个月入职或是周年入职
                if (Carbon::today()->format('m') == Carbon::parse($employ)->format('m')) {
                    if (Carbon::today()->format('Y') != Carbon::parse($employ)->format('Y')) {
                        $this->createLibrary($user, Coupon::FLAG['hey_tea_fee_join_anniversary'], 1); //周年入职
                    }
                }
                //司庆
                if (Carbon::today()->format('m') == 5) {
                    $this->createLibrary($user, Coupon::FLAG['hey_tea_fee_day'], 1); //司庆
                }
            }
            if (count($res) > 0) {
                IQuery::redisSet($redis_path, $res);
            } else {
                IQuery::redisDelete($redis_path);
            }
            DB::commit();
            Log::info('shop_welfare_success', ['success']);
        } catch (\Exception $exception) {
            DB::rollback();
            Log::info('shop_welfare_error', [$exception]);
        } 
    }
    
    /**
     * 创建个人优惠券库
     */
    public function createLibrary($user, $flag, $count) {
        $coupon = Coupon::where('flag', $flag)->first();
        //新建模板个人库
        for($i = 0; $i< $count; $i++) {
            $libraryArr[] = [
                'name' => $coupon->name,
                'user_id' => $user->id,
                'order_id' => 0,
                'coupon_id' => $coupon->id,
                'policy' => $coupon->policy,
                'policy_rule' => json_encode($coupon->policy_rule),
                'source_id' => $coupon->id,
                'source_type' => Coupon::class,
                'period_start' => Carbon::today(),
                'period_end' => Carbon::today()->addMonth(1),
                'status' => CouponLibrary::STATUS['surplus'],
                'code_id' => $coupon->no.IQuery::strPad(date('ymd').rand(100, 999)),
                'tab' => CouponLibrary::NEWTAB['new'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }
        DB::table('coupon_librarys')->insert($libraryArr);
        $member = $user->members->first() ?? '';
        if ($member != '') {
            $member->update(['new_coupon_tab' => Member::NEWTAB['new']]);
        }
    }
}
