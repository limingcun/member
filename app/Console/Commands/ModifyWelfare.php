<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coupon;
use App\Models\CouponLibrary;
use App\Models\Member;
use App\Policies\CouponLibrary\CashCouponPolicy;
use Carbon\Carbon;
use IQuery;
use Log;
use DB;
use App\Models\User;
use Maatwebsite\Excel\Excel;

class ModifyWelfare extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modify:welfare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '调整会员福利';

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
    public function handle(Excel $excel)
    {
        DB::beginTransaction();
        try {
            $fileName = 'public/excel/istore.xlsx';
            if (!is_file($fileName)) {
                return;
            }
            $reader = $excel->load($fileName);
            $ones = $reader->getSheet(0)->toArray();
            $twos = $reader->getSheet(1)->toArray();
            $threes = $reader->getSheet(2)->toArray();
            $fours = $reader->getSheet(3)->toArray();
            $fives = $reader->getSheet(4)->toArray();
            $sixs = $reader->getSheet(5)->toArray();
            $sevens = $reader->getSheet(6)->toArray();
            $eights = $reader->getSheet(7)->toArray();
            //门店5张25元代金
            foreach($ones as $k => $one) {
                $phone = $one[3]; //电话
                if ($phone == '') {
                    continue;
                }
                $user = User::where('phone', $phone)->first();
                if ($user) {
                    $this->createLibrary($user, Coupon::FLAG['hey_tea_cash_25'], 5); //25元代金券(5张)
                }
            }
            //两张5折
            foreach($twos as $k => $two) {
                $phone = $two[3]; //电话
                if ($phone == '') {
                    continue;
                }
                $user = User::where('phone', $phone)->first();
                if ($user) {
                    $this->createLibrary($user, Coupon::FLAG['hey_tea_discount_5'], 2);
                }
            }
            //生日赠饮
            foreach($threes as $k => $three) {
                $phone = $three[3]; //电话
                if ($phone == '') {
                    continue;
                }
                $user = User::where('phone', $phone)->first();
                if ($user) {
                    $this->createLibrary($user, Coupon::FLAG['hey_tea_fee_birthday'], 1);
                }
            }
            //周年赠饮
            foreach($fours as $k => $four) {
                $phone = $four[3]; //电话
                if ($phone == '') {
                    continue;
                }
                $user = User::where('phone', $phone)->first();
                if ($user) {
                    $this->createLibrary($user, Coupon::FLAG['hey_tea_fee_join_anniversary'], 1);
                }
            }
            //1月下旬入职券
            foreach($fives as $k => $five) {
                $phone = $five[3]; //电话
                if ($phone == '') {
                    continue;
                }
                $user = User::where('phone', $phone)->first();
                if ($user) {
                    $this->createLibrary($user, Coupon::FLAG['hey_tea_fee_join_day'], 1);
                }
            }
            //1月补发（5张25元代金券）
            foreach($sixs as $k => $six) {
                $phone = $six[3]; //电话
                if ($phone == '') {
                    continue;
                }
                $user = User::where('phone', $phone)->first();
                if ($user) {
                    $this->createLibrary($user, Coupon::FLAG['hey_tea_cash_25'], 5);
                }
            }
            //1月补发（两张5折）
            foreach($sevens as $k => $seven) {
                $phone = $seven[3]; //电话
                if ($phone == '') {
                    continue;
                }
                $user = User::where('phone', $phone)->first();
                if ($user) {
                    $this->createLibrary($user, Coupon::FLAG['hey_tea_discount_5'], 2);
                }
            }
            //优先制作
            foreach($eights as $k => $eight) {
                $phone = $eight[3]; //电话
                $count = $eight[4]; //数量
                if ($phone == '') {
                    continue;
                }
                $user = User::where('phone', $phone)->first();
                if ($user) {
                    $this->createLibrary($user, Coupon::FLAG['hey_tea_queue'], $count);
                }
            }
            DB::commit();
            Log::info('MODIFY_WELFARE_SUCCESS', ['SUCCESS']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info('MODIFY_WELFARE_ERROR', [$e]);
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