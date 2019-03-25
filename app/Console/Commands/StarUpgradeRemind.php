<?php

namespace App\Console\Commands;

use App\Models\GiftRecord;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Console\Command;

class StarUpgradeRemind extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remind:star:upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '星球会员升级提醒';

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
        GiftRecord::where('gift_type', GiftRecord::GIFT_TYPE['star_update'])
            ->where('start_at', '<=', Carbon::now()->addMinutes(5))
            ->where('start_at', '>=', Carbon::now())->chunk(200, function ($records) {
                foreach ($records as $record) {
                    Message::starUpgradeMsg($record->user_id);
                    \DB::table('members')
                        ->where('user_id', $record->user_id)
                        ->increment('message_tab', 1);
                }
            });
    }
}
