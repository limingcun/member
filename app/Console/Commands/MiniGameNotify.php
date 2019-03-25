<?php

namespace App\Console\Commands;

use App\Models\CouponLibrary;
use App\Models\User;
use App\Services\MiniGame;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MiniGameNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mini_game_notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '使用优惠券通知小游戏';


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
        if (env('MINI_GAME_OPEN')) {
            $librarys = CouponLibrary::where('used_at', '>', Carbon::now()->subMinute(2))->get();
            $MiniGame = new MiniGame();
            foreach ($librarys as $library) {
                if (\Cache::get("mini_game_coupon:$library->coupon_id")) {
                    $lockKey = "mini_game_lock:$library->id";
                    if (!\Cache::get($lockKey)) {
                        \Cache::put($lockKey, 1, 5);
                        $MiniGame->useCoupon(User::find($library->user_id), $library->coupon_id);
                    }
                }
            }
        }
    }
}
