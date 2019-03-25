<?php

/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/11/29
 * Time: 上午10:13
 * desc: 设置积分经验值补录区间
 */
namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;
use DB;
use IQuery;

class SetAddRecord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:record {start?} {end?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '设置积分经验值补录区间';
    
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
        DB::beginTransaction();
        try {
            $start = $this->argument('start');  //获取开始时间
            $end = $this->argument('end'); //获取结束时间
            $start = Carbon::parse($start)->startOfDay();
            $end = Carbon::parse($end)->endOfDay();
            $sql = "select o.id, o.user_id, o.payment, o.delivery_fee, o.created_at, o.updated_at from orders o left join member_scores ms on o.id = ms.source_id where o.paid_at is not null and "
                 . "o.refund_status = 'NO_REFUND' and o.user_id > 0 and ms.id is null and o.created_at between '".$start."' and '".$end."'";
            $res = DB::select($sql);
            IQuery::redisSet($redis_path, $res);
            DB::commit();
            Log::info('set_add:record_success', ['success']);
        } catch (\Exception $exception) {
            DB::rollback();
            Log::info('set_add:record_error', [$exception]);
        } 
    }
}
