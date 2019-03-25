<?php

/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/11/16
 * Time: 上午11:04
 * desc: 星球会员过期
 */
namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Member;
use Illuminate\Console\Command;
use Log;
use DB;

class ExpireStarMember extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expire:star';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '星球会员过期';

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
     * @return mixed
     */
    public function handle()
    {
        $stars = Member::whereNotNull('expire_time')->where('member_type', 1)->where('expire_time', '<', Carbon::today())->get();
        if ($stars->isEmpty()) {
            return;
        }
        DB::beginTransaction();
        try {
            $starIds = [];
            foreach($stars as $star) {
                $starIds[] = $star->id;
            }
            if (count($starIds) > 0) {
                Member::whereIn('id', $starIds)->update(['member_type' => 0]);
            }
            DB::commit();
            Log::info('STAR:EXPIRE_SUCCESS', ['SUCCESS']);
        } catch (\Exception $exception) {
            DB::rollback();
            Log::info('STAR:EXPIRE_ERROR', [$exception]);
        }
    }
}
