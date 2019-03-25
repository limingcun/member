<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StarLevel;
use App\Http\Repositories\Admin\StarLevelRepository;
use Carbon\Carbon;
use IQuery;

/*
 * 星球会员等级控制器
 */
class StarLevelController extends Controller
{
    protected $redis_path = 'laravel:memberstarlevel:';
    
    /*
     * 星球会员等级列表数据
     */
    public function index() {
        $star_level = StarLevel::withCount(['member as star_count' => function($query) {
            $query->whereDate('expire_time', '>=', Carbon::today());
        }])->get();
        $rps = new StarLevelRepository();
        $star_level = $rps->levelList($star_level);
        return response()->json($star_level);
    }

    /*
     * 用户数量和星球会员数量增长点
     * 日增长点(24个点)
     * 周增长点(7个点)
     * 月增长点(30个点)
     * 年增长点(12)个点
     * $t周月年(1,2,3)
     */
    public function starLevelIncrease(Request $request) {
        $t = $request->t;
        $star_level_id = $request->star_level_id;
        $sdate = $this->redis_path.'star_level'.$t.Carbon::today()->timestamp.$star_level_id;
        $pointArr = IQuery::redisGet($sdate);
        if (!$pointArr) {
            $rps = new StarLevelRepository();
            switch($t) {
                case 1:
                case 2:
                    $pointArr = $rps->weekOrMonthIncrease($t, $star_level_id);
                    break;
                case 3:
                    $pointArr = $rps->yearIncrease($star_level_id);
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
