<?php

/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/12/28
 * Time: 下午20:38
 * desc: 设置积分经验值补录区间
 */
namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;
use DB;
use IQuery;

class DirlyAddRecord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dirly:record';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '凌晨积分经验值补录';
    
    protected $redis_path = 'laravel:add_record:';

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
        $redis_path = $this->redis_path.'score_star_exp';
        if (IQuery::redisExists($redis_path)) {
            return;
        }
        $start = Carbon::yesterday()->startOfDay();
        $end = Carbon::yesterday()->endOfDay();
        DB::beginTransaction();
        try {
            $sql = "select o.id, o.user_id, o.payment, o.delivery_fee, o.created_at, o.updated_at from orders o left join member_scores ms on o.id = ms.source_id where o.paid_at is not null and "
                 . "o.refund_status = 'NO_REFUND' and o.user_id > 0 and ms.id is null and o.created_at between '".$start."' and '".$end."'";
            $res = DB::select($sql);
            if (count($res) > 0) {
                IQuery::redisSet($redis_path, $res);
            }
            DB::commit();
            Log::info('dirly_add:record_success', ['success']);
        } catch (\Exception $exception) {
            DB::rollback();
            Log::info('dirly_add:record_error', [$exception]);
        } 
    }
}
