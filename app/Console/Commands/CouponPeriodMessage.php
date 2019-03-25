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
use App\Models\Message;

class CouponPeriodMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'period:message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '喜茶券到期通知';

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
        CouponLibrary::where('status', CouponLibrary::STATUS['surplus'])
            ->whereDate('period_end', '<=', Carbon::today()->addDay(4))->whereDate('period_end', '>=', Carbon::today())->groupBy('user_id')->chunk(200, function($librarys) {
                foreach($librarys as $library) {
                    Message::couponsOverdueMsg($library->user_id);
                    $member = Member::where('user_id', $library->user_id)->first();
                    $member->update(['message_tab' => $member->message_tab + 1]);
                }
            });
    }
}
