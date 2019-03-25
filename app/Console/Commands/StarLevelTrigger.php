<?php

/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/12/03
 * Time: 下午18:57
 * desc: 等级触发器
 */
namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use DB;
use App\Models\GiftRecord;
use App\Models\Level;
use App\Models\Member;
use App\Models\StarLevel;
use IQuery;
use Log;

class StarLevelTrigger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'star_level:trigger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '星球等级触发器';

    protected $redis_path = 'laravel:level_trigger:';

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
        $redis_path = $this->redis_path.'star';
        if (!IQuery::redisExists($redis_path)) {
            return;
        }
        DB::beginTransaction();
        try {
            $res = IQuery::redisRange($redis_path, 0, -1);
            IQuery::redisDelete($redis_path);
            foreach($res as $r) {
                $member = Member::find($r);
                starLevel($member);
            }
            DB::commit();
            Log::info('star_level_success', ['success']);
        } catch (\Exception $exception) {
            DB::rollback();
            Log::info('star_level_error', [$exception]);
        }
    }
}
