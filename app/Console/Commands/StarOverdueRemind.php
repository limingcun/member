<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Console\Command;

class StarOverdueRemind extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remind:star:overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '星球会员过期提醒';

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
        Member::where('expire_time', Carbon::now()->addDays(14))
            ->chunk(200, function ($members) {
               foreach ($members as $member) {
                   Message::starOverdueMsg($member->user_id);
                   \DB::table('members')
                       ->where('user_id', $member->user_id)
                       ->increment('message_tab', 1);
               }
            });
    }
}
