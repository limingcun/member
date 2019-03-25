<?php

/**
 * Created by netbeanIDE.
 * User: limingcun
 * Date: 2018/5/24
 * Time: 下午16:39
 * desc: 优惠券失效定时器
 */
namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\CouponLibrary;
use Illuminate\Console\Command;
use App\Models\Member;
use Log;
use DB;
use IQuery;

class InvalidCouponLibrary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invalid:coupon_library';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '优惠券失效';
    
    protected $redis_path = 'laravel:coupon_library:';

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
     * 设置优惠券失效字段status=3为失效状态
     * @return mixed
     */
    public function handle()
    {
        if (IQuery::redisGet($this->redis_path.'period_coupon')) {
            return;
        }
        $res = CouponLibrary::whereIn('status', [CouponLibrary::STATUS['unpick'], CouponLibrary::STATUS['surplus']])
             ->where('period_end', '<', Carbon::today())
             ->select('id')->paginate(1000);
        if($res->isEmpty()) {
            return;
        }
        $page_num = $res->lastPage();
        if ($page_num > 1) {
            IQuery::redisSet($this->redis_path.'period_coupon', 1);
        }
        DB::beginTransaction();
        try {
            $arrLibraryId = [];  //过期优惠券数组
            $arrUserId = []; //过期用户数组
            $uptUserId = []; //更新用户数据
            foreach($res as $k => $r) {
                $arrLibraryId[] = $r->id;
                if (!in_array($r->user_id, $arrUserId)) {
                    $arrUserId[] = $r->user_id;
                }
            }
            if (count($arrLibraryId) > 0) {
                CouponLibrary::whereIn('id', $arrLibraryId)->update([
                    'status' => CouponLibrary::STATUS['period'],
                    'tab' => CouponLibrary::NEWTAB['scan']
                ]);
            }
            foreach($arrUserId as $user_id) {
                $library = CouponLibrary::where('user_id', $user_id)->where('tab', CouponLibrary::NEWTAB['new'])->first();
                if (!$library) {
                    $uptUserId[] = $user_id;
                }
            }
            if (count($uptUserId) > 0) {
                Member::whereIn('user_id', $uptUserId)->update(['new_coupon_tab' => Member::NEWTAB['scan']]);
            }
            DB::commit();
            Log::info('lose:coupon_success', ['success']);
        }
        catch (\Exception $exception) {
            DB::rollback();
            Log::info('lose:coupon_error', [$exception]);
        } 
    }
}
