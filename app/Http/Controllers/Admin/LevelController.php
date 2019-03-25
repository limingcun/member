<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Models\Level;
use App\Models\LevelRule;
use App\Models\Member;
use App\Http\Requests\Admin\LevelRequest;
use App\Http\Requests\Admin\LevelRuleRequest;
use App\Http\Repositories\Admin\LevelRepository;
use IQuery;
use Carbon\Carbon;
use DB;

class LevelController extends ApiController
{
    protected $redis_path = 'laravel:memberlevel:';
    
    /*
     * 会员等级列表数据
     */
    public function index()
    {
        $rps = new LevelRepository();
        $levels = Level::get();
        foreach ($levels as $level) {
            $level->equity = $rps->equity($level);
        }
        return response()->json($levels);
    }

    /*
     * 会员等级比例
     */
    public function levelRatia()
    {
        $rps = new LevelRepository();
        return $rps->levelRatia();
    }

    /*
     * 用户数量和星球会员数量增长点
     * 日增长点(24个点)
     * 周增长点(7个点)
     * 月增长点(30个点)
     * 年增长点(12)个点
     * $t周月年(1,2,3)
     */
    public function levelIncrease(Request $request)
    {
        $t = $request->t;
        $level_id = $request->level_id;
        $sdate = $this->redis_path.'level'.$t.Carbon::today()->timestamp.$level_id;
        $pointArr = IQuery::redisGet($sdate);
        if (!$pointArr) {
            $rps = new LevelRepository();
            switch ($t) {
                case 1:
                case 2:
                    $pointArr = $rps->weekOrMonthIncrease($t, $level_id);
                    break;
                case 3:
                    $pointArr = $rps->yearIncrease($level_id);
                    break;
                default:
                    $pointArr = '';
                    break;
            }
            IQuery::redisSet($sdate, $pointArr, 3600 * 24);
        }
        return response()->json($pointArr);
    }
}
