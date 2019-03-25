<?php
namespace App\Console\Commands;
use App\Models\GiftRecord;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Console\Command;
use DB;
class StarWelfare extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'star:welfare';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '星球会员每月福利';
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
     */
    public function handle()
    {
        // 查找GiftRecord表中是否有start_at 等于今天 未领取的 星球会员每月福利 礼包 有就发放
        GiftRecord::where('start_at', '<=', Carbon::today()->endOfDay())->where('gift_type', GiftRecord::GIFT_TYPE['star_monthly_welfare'])
            ->whereNull('pick_at')->select()->chunk(200, function ($gift_records) {
                foreach ($gift_records as $gift_record) {
                    $member = Member::where('user_id', $gift_record->user_id)->select(['id', 'star_level_id'])
                        ->where('expire_time', '>=', Carbon::today())->first();
                    if ($member) {
                        $this->starMonthlyWelfare($member->starLevel->name, $gift_record->user_id);
                        // 领取成功后更新礼包状态 记录领取的什么等级的每月福利
                        $gift_record->update(['star_level_id' => $member->star_level_id, 'pick_at' => Carbon::now()]);
                    }
                }
            });
    }

    /**
     * 星球会员每月福利礼包
     */
    public function starMonthlyWelfare($star_level, $user_id)
    {
        switch ($star_level) {
            case '白银':
                createCoupon('discount_star_month', $user_id, 1);
                createCoupon('cash_150-5', $user_id, 2);
                break;
            case '黄金':
                createCoupon('discount_star_month', $user_id, 2);
                createCoupon('queue_star_month', $user_id, 1);
                createCoupon('cash_150-10', $user_id, 2);
                break;
            case '铂金':
                createCoupon('discount_star_month', $user_id, 3);
                createCoupon('queue_star_month', $user_id, 1);
                createCoupon('buy_fee_3-1', $user_id, 2);
                createCoupon('cash_150-15', $user_id, 3);
                break;
            case '钻石':
                createCoupon('discount_star_month', $user_id, 3);
                createCoupon('queue_star_month', $user_id, 2);
                createCoupon('buy_fee_2-1', $user_id, 2);
                createCoupon('cash_150-20', $user_id, 3);
                break;
            case '黑金':
                createCoupon('discount_star_month', $user_id, 5);
                createCoupon('queue_star_month', $user_id, 2);
                createCoupon('buy_fee_2-1', $user_id, 3);
                createCoupon('cash_150-25', $user_id, 3);
                break;
            case '黑钻':
                createCoupon('discount_star_month', $user_id, 6);
                createCoupon('queue_star_month', $user_id, 3);
                createCoupon('buy_fee_1-1', $user_id, 2);
                createCoupon('cash_150-30', $user_id, 3);
                // 黑钻有两张会员纪念日券
                blackDiamondAnniversaryCoupon($user_id);
                break;
            default:
                break;
        }
    }
}
