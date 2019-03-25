<?php

namespace App\Console\Commands;

use App\Models\GiftRecord;
use App\Models\Member;
use App\Models\MemberCardRecord;
use App\Models\User;
use App\Models\VkaRecord;
use Carbon\Carbon;
use Illuminate\Console\Command;
use DB;

class StarMemberOffset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'star:offset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '星球会员补偿';

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
        DB::beginTransaction();
        try{
            // 买卡 + 迁移的 把会员卡的时间累加上 同时每月福利也累加上
            VkaRecord::where('status', 1)->distinct('user_id')->chunk(200, function ($records) {
                foreach ($records as $record) {
                    $card_record = MemberCardRecord::where('user_id', $record->user_id)->where('status', MemberCardRecord::STATUS['is_pay'])
                        ->where('card_type', '!=', MemberCardRecord::CARD_TYPE['vka'])->first();
                    if (isset($card_record)) {
                        switch ($card_record->card_type) {
                            case MemberCardRecord::CARD_TYPE['season']:
                                $months = 3; break;
                            case MemberCardRecord::CARD_TYPE['half_year']:
                                $months = 6; break;
                            case MemberCardRecord::CARD_TYPE['annual']:
                                $months = 12; break;
                            default:
                                $months = 0;break;
                        }
                        $member = Member::where('user_id', $card_record->user_id)->first();
                        $member->expire_time = Carbon::createFromTimestamp(strtotime($member->expire_time))->addMonthsNoOverflow($months)->format('Y-m-d');
                        $member->save();
                        $user = User::where('id', $card_record->user_id)->first();
                        createMonthGift($card_record->card_type, $user);
                    }
                }
            });

            // 2018-12-04 迁移的用户 会员等级为黄金及以上  删除原来的每月福利券 根据最新等级补发每月福利券
            Member::whereMonth('star_time', 12)->whereDay('star_time', 4)->where('member_type', 2)->where('star_level_id', '>=', 2)
                ->chunk(200, function ($membres) {
                    foreach ($membres as $membre) {
                        $is_exists = GiftRecord::where('user_id', $membre->user_id)->whereMonth('created_at', 12)
                            ->whereDay('created_at', 4)->select('id')->exists();
                        if ($is_exists) {
                            // 补发当前等级的每月福利
                            starMonthlyWelfare($membre->star_level_id, $membre->user_id, MemberCardRecord::CARD_TYPE['season']);
                        }
                    }
                });
            DB::commit();
        } catch (\Exception $e) {
            \Log::error('补偿失败! ', [$e]);
            DB::rollBack();
        }

    }
}
