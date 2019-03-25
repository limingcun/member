<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use App\Models\CouponLibrary;
use Carbon\Carbon;
use Illuminate\Console\Command;

class StarBirthdayGift extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'star:birthday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '星球会员生日好礼';

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
        // 星球会员 && 生日在本月
        // 生日当天才可以使用
        $today = Carbon::today()->toDateString();
        \DB::table('members as m')->select(['m.id', 'm.user_id', 'u.birthday'])
            ->leftJoin('users as u', 'u.id', '=','m.user_id')
            ->where('m.expire_time', '>=', "$today")->orderByDesc('m.id')
            ->whereMonth('u.birthday',  date('m'))->chunk(100, function ($members) {
                $coupon_id = Coupon::where('flag', Coupon::FLAG['fee_star_birthday'])->value('id');
                foreach ($members as $member) {
                    $is_exists = CouponLibrary::where('user_id', $member->user_id)->where('coupon_id', $coupon_id)
                        ->whereMonth('period_start', date('m'))->whereYear('period_start', date('Y'))->exists();
                    // 确保不会重复发放生日券
                    if (!$is_exists) {
                        $birth = strtotime($member->birthday);
                        createCoupon('fee_star_birthday', $member->user_id, 1,
                            Carbon::create(date('Y'), date('m', $birth), date('d', $birth))->endOfDay(),
                            Carbon::create(date('Y'), date('m', $birth), date('d', $birth))->startOfDay());
                    }
                }
            });
    }
}
