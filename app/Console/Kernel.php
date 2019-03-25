<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\SetUserCoupon::class,
        Commands\DelayCoupon::class,
        //Commands\ClearScore::class,
        Commands\InvalidCouponLibrary::class,
        Commands\DelayPeriodCouponLibrary::class,
        Commands\PeriodActive::class,
       // Commands\DeleteLog::class,
        Commands\PeriodMall::class,
        Commands\FinishExpress::class,
        Commands\AddressUpdate::class,
        Commands\ClearOrderLock::class,
        Commands\StarWelfare::class,
        Commands\StarBirthdayGift::class,
        Commands\PeriodCoupon::class,
        Commands\PeriodCouponGrand::class,
        Commands\GiftHandle::class,
        Commands\ExpireStarMember::class,
        Commands\ClosedCardOrder::class,
        Commands\AddRecord::class,
        Commands\DepartmentWelfare::class,
        Commands\ShopWelfare::class,
        Commands\GiftEmployeeWelfare::class,
        Commands\CancelCouponLibrary::class,
        Commands\GoLevelTrigger::class,
        Commands\StarLevelTrigger::class,
        Commands\MiniGameNotify::class,
        Commands\DirlyAddRecord::class,
        Commands\CouponPeriodMessage::class,
        Commands\GoUpgradeRemind::class,
        Commands\StarUpgradeRemind::class,
        Commands\StarOverdueRemind::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        Log::info('=====定时任务开始====');
        // 定时发券
        $schedule->command('set:coupon')->everyMinute();
        $schedule->command('delay:coupon')->everyMinute();
        //活动开启和失效
        $schedule->command('period:active')->daily();
        //优惠券模板失效
        $schedule->command('period:coupon')->daily();
        //发券过期失效
        $schedule->command('period:coupon_grand')->daily();
        // 个人优惠券失效
        $schedule->command('invalid:coupon_library')->daily();
        //清理订单锁
        $schedule->command('clr:orderLock')->everyMinute();
        //快递单的状态
        $schedule->command('delay:express')->daily();
        // 积分商城商品过期定时下架
        $schedule->command('period:mall')->daily();
        //个人优惠券延迟失效
        $schedule->command('delay_period_coupon_library')->everyMinute();
        // 星球会员过期
        $schedule->command('expire:star')->daily();
        // 星球会员生日好礼
        $schedule->command('star:birthday')->monthlyOn(1, '0:0');
        // 星球会员每月福利
        $schedule->command('star:welfare')->everyThirtyMinutes();
        // 星球会员升级券包 处理
        $schedule->command('gift:handle')->everyMinute();
        // 关闭星球会员购买订单
        $schedule->command('close:card_order')->everyMinute();
        // 积分经验值补录定时器
        $schedule->command('add:record')->everyMinute();
        // 查询未核销券进行核销
        $schedule->command('cancel:coupon_library')->everyMinute();
        // go定时升级
        $schedule->command('go_level:trigger')->everyMinute();
        // 星球定时升级
        $schedule->command('star_level:trigger')->everyMinute();
        //使用优惠券通知小游戏
        $schedule->command('mini_game_notify')->everyMinute();
        //凌晨积分经验值补录
        $schedule->command('dirly:record')->daily();
        //优惠券到期提示通知
        $schedule->command('period:message')->daily();
        // go会员升级提醒
        $schedule->command('remind:go:upgrade')->everyMinute();
        // 星球会员升级提醒
        $schedule->command('remind:star:upgrade')->everyMinute();
        // 星球会员过期提醒
        $schedule->command('remind:star:overdue')->daily()->at('5:28');
        Log::info('=====定时任务结束====');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
