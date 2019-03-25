<?php

namespace App\Console\Commands;

use App\Models\GiftRecord;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GiftHandle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gift:handle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '礼包处理器';

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
        // 星球会员升级礼包触发
        GiftRecord::where('gift_type', GiftRecord::GIFT_TYPE['star_update'])->whereNull('pick_at')
            ->where('start_at','<=', Carbon::now())->select(['id', 'user_id', 'star_level_id'])->chunk(100, function ($gifts) {
                $now = Carbon::now();
                foreach ($gifts as $gift) {
                    $this->starUpdate($gift->starLevel->name, $gift->user_id);
                    $gift->update(['pick_at' => $now]);
                }
            });
    }

    /**
     * 星球会员升级礼包（瞬间礼包）
     */
    public function starUpdate($star_level, $user_id)
    {
        $method = 'star_update';
        $description = '星球会员升级瞬间礼包';
        switch ($star_level) {
            case '黄金':
                createPoint($user_id, 200, $method, $description);
                createCoupon('fee_star_update', $user_id, 1);
                break;
            case '铂金':
                createPoint($user_id, 300, $method, $description);
                createCoupon('fee_star_update', $user_id, 2);
                break;
            case '钻石':
                createPoint($user_id, 400, $method, $description);
                createCoupon('fee_star_update', $user_id, 2);
                break;
            case '黑金':
                createPoint($user_id, 500, $method, $description);
                createCoupon('fee_star_update', $user_id, 3);
                break;
            case '黑钻':
                createPoint($user_id, 1000, $method, $description);
                createCoupon('fee_star_update', $user_id, 3);
                break;
            default:
                break;
        }
    }

}
