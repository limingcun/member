<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Repositories\Admin\CashStorageRepository;
use IQuery;
use Carbon\Carbon;

/*
 * 储值控制器
 */
class CashStorageController extends ApiController
{
    protected $redis_path = 'laravel:storagedata:';
    /*
     * 储值列表数据
     */
    public function index(Request $request) {
        $rps = new CashStorageRepository();
        $cash_storages = $rps->index($request);
        return $this->response->collection($cash_storages);
    }
    
    /**
     * 用户详情
     * @param type $user_id
     * @return type
     */
    public function show($id) {
        $rps = new CashStorageRepository();
        $cash_storage = $rps->show($id);
        return $this->response->item($cash_storage);
    }


    /*
     * 储值用户总数
     * @param Request $request
     * @return object
     */
    public function storageNum()
    {
        $rps = new CashStorageRepository();
        $res = $rps->storageNum();
        return $res;
    }
    
    
    /*
     * 储值用户数量增长点
     * 日增长点(24个点)
     * 周增长点(7个点)
     * 月增长点(30个点)
     * 年增长点(12)个点
     * $t日周月年(0,1,2,3)
     */
    public function storageIncrease(Request $request) {
        $t = $request->t;
        $sdate = $this->redis_path.'_'.$t.Carbon::today()->timestamp;
        $pointArr = IQuery::redisGet($sdate);
        if (!$pointArr) {
            $rps = new CashStorageRepository();
            switch($t) {
                case 0:
                    $pointArr = $rps->dateIncrease(24);
                    break;
                case 1:
                case 2:
                    $pointArr = $rps->weekOrMonthIncrease(Carbon::yesterday()->format('d'), $t);
                    break;
                case 3:
                    $pointArr = $rps->yearIncrease(Carbon::today()->format('m'));
                    break;
                default:
                    $pointArr = '';
                    break;
            }
            IQuery::redisSet($sdate, $pointArr, 3600 * 24);
        }
        return response()->json($pointArr);
    }
    
    /**
     * 金额比例
     */
    public function storageRate() {
        $rps = new CashStorageRepository();
        $res = $rps->RechargeAmount();
        return $res;
    }
    
    /**
     * 用户累计充值金额,消费金额,余额
     */
    public function feeAddUp() {
        $rps = new CashStorageRepository();
        $res = $rps->feeAddUp();
        return $res;
    }
}
