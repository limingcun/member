<?php

namespace App\Http\Repositories\Admin;

use Illuminate\Http\Request;
use App\Models\StarLevel;
use App\Http\Repositories\BaseRepository;
use Carbon\Carbon;
use DB;

class StarLevelRepository extends BaseRepository
{
    protected $table;

    public function __construct()
    {
        $this->table = new StarLevel();
    }

    public function index(Request $request)
    {
    }


    /**
     * 星球会员等级列表
     */
    public function levelList($star_levels)
    {
        foreach ($star_levels as $star_level) {
            $star_level['equity'] = $this->equity($star_level['name']);
            unset($star_level->updated_at);
            unset($star_level->deleted_at);
        }
        return $star_levels;
    }

    /**
     * 星球会员等级权益说明
     */
    public function equity($star_level)
    {
        $arr = [];
        $arr['privilege'] = [
            '积分与经验值涨速翻倍',
        ];
        $arr['welfare'] = [
            '5.12会员日赠饮券x1',
            '生日好礼：免费赠饮券x1',
        ];
        $arr['update'] = array();
        switch ($star_level) {
            case '白银':
                $arr['privilege'][] = '每月12日会员日额外奉送5%积分';
                $arr['welfare'][] = '每月指定饮品立减券x1';
                $arr['welfare'][] = '星球会员开通纪念日赠饮券x1';
                $arr['welfare'][] = '满150减5元券x2';
                break;
            case '黄金':
                $arr['privilege'][] = '每月12日会员日额外奉送10%积分';
                $arr['privilege'][] = '外卖配送费9折';
                $arr['welfare'][] = '每月指定饮品立减券x2';
                $arr['welfare'][] = '星球会员开通纪念日赠饮券x1';
                $arr['welfare'][] = '满150减10元券x2';
                $arr['welfare'][] = '每月优先券x1';
                $arr['update'] = [
                    '送积分200',
                    '赠饮券x1'
                ];
                break;
            case '铂金':
                $arr['privilege'][] = '每月12日会员日额外奉送15%积分';
                $arr['privilege'][] = '外卖配送费7折';
                $arr['welfare'][] = '每月指定饮品立减券x3';
                $arr['welfare'][] = '星球会员开通纪念日赠饮券x1';
                $arr['welfare'][] = '满150减15元券x3';
                $arr['welfare'][] = '每月优先券x1';
                $arr['welfare'][] = '买三赠一券x2';
                $arr['update'] = [
                    '送积分300',
                    '赠饮券x2'
                ];
                break;
            case '钻石':
                $arr['privilege'][] = '每月12日会员日额外奉送20%积分';
                $arr['privilege'][] = '外卖配送费5折';
                $arr['welfare'][] = '每月指定饮品立减券x3';
                $arr['welfare'][] = '星球会员开通纪念日赠饮券x1';
                $arr['welfare'][] = '满150减20元券x3';
                $arr['welfare'][] = '每月优先券x2';
                $arr['welfare'][] = '买二送一券x2';
                $arr['welfare'][] = '每20单送1张赠饮券';
                $arr['update'] = [
                    '送积分400',
                    '赠饮券x2'
                ];
                break;
            case '黑金':
                $arr['privilege'][] = '每月12日会员日额外奉送25%积分';
                $arr['privilege'][] = '外卖配送费3折';
                $arr['privilege'][] = '喜茶会员活动优先报名权';
                $arr['privilege'][] = '商城指定商品兑换';
                $arr['welfare'][] = '每月指定饮品立减券x5';
                $arr['welfare'][] = '星球会员开通纪念日赠饮券x1';
                $arr['welfare'][] = '满150减25元券x3';
                $arr['welfare'][] = '每月优先券x2';
                $arr['welfare'][] = '买二送一券x3';
                $arr['welfare'][] = '每10单送1张赠饮券';
                $arr['update'] = [
                    '送积分500',
                    '赠饮券x3'
                ];
                break;
            case '黑钻':
                $arr['privilege'][] = '每月12日会员日额外奉送30%积分';
                $arr['privilege'][] = '外卖免配送费';
                $arr['privilege'][] = '喜茶会员活动优先报名权';
                $arr['privilege'][] = '商城指定商品兑换';
                $arr['welfare'][] = '每月指定饮品立减券x6';
                $arr['welfare'][] = '星球会员开通纪念日赠饮券x2';
                $arr['welfare'][] = '满150减30元券x3';
                $arr['welfare'][] = '每月优先券x3';
                $arr['welfare'][] = '买一送一券x2';
                $arr['welfare'][] = '每5单送1张赠饮券';
                $arr['update'] = [
                    '送积分1000',
                    '赠饮券x3'
                ];
                break;
            default:
                break;
        }
        $arr['equity_count'] = count($arr['privilege']) + count($arr['welfare']) + count($arr['update']);
        return $arr;
    }


    /*
     * 周或月用户数据增长点
     * $num是数量
     * $type是总用户还是星球会员
     * $t是周或月类型
     */
    public function weekOrMonthIncrease($t, $star_level_id = '')
    {
        $randArr = [];
        if ($t == 1) {
            $start = Carbon::today()->subWeek(1);
            $end = Carbon::yesterday()->endOfDay();
            $s = $start->format('d');
            for ($i = 0; $i < 7; $i++) {
                $randArr[] = Carbon::today()->subWeek(1)->addDay($i)->format('Y-m-d');
            }
        } else if ($t == 2) {
            $start = Carbon::today()->subMonth(1);
            $end = Carbon::yesterday()->endOfDay();
            $s = $start->format('d');
            $ts = Carbon::now()->subMonth()->lastOfMonth()->format('d') - $s + Carbon::today()->format('d');
            for ($i = 0; $i < $ts; $i++) {
                $randArr[] = Carbon::today()->subMonth(1)->addDay($i)->format('Y-m-d');
            }
        }
        $uarr = [];
        $sql = "SELECT DISTINCT(DATE_FORMAT(m.star_time,'%Y-%m-%d')) as time, count(1) as num from members as m where m.created_at BETWEEN '" .$start. "' and '" .$end. "'";
        if ($star_level_id != '') {
            $sql = $sql . " and m.star_level_id = " . $star_level_id;
        }
        $sql = $sql . " GROUP BY time";
        $res = DB::select($sql);
        foreach ($res as $r) {
            if (Carbon::parse($r->time)->timestamp >= Carbon::parse($start)->timestamp && Carbon::parse($r->time)->timestamp < Carbon::parse($end)->timestamp) {
                $uarr[$r->time] = $r->num;
            }
        }
        $arr = $this->forWeekMonthYearArr($uarr, $randArr);
        return $arr;
    }

    /*
    * 年用户数据增长点
    * $num是数量
    * $type是总用户还是星球会员
    */
    public function yearIncrease($star_level_id = '')
    {
        $randArr = [];
        $start = Carbon::today()->subYear(1);
        $end = Carbon::today()->subMonth(1);
        for ($i = 0; $i < 12; $i++) {
            $randArr[] = Carbon::today()->subYear(1)->addMonth($i)->format('Y-m');
        }
        $uarr = [];
        $sql = "SELECT DISTINCT(DATE_FORMAT(m.star_time,'%Y-%m')) as time, count(1) as num from members as m";
        if ($star_level_id != '') {
            $sql = $sql . " where star_level_id = " . $star_level_id;
        }
        $sql = $sql . " GROUP BY time";
        $res = DB::select($sql);
        foreach ($res as $r) {
            if ($r->time >= $start && $r->time < $end) {
                $uarr[$r->time] = $r->num;
            }
        }
        $arr = $this->forWeekMonthYearArr($uarr, $randArr);
        return $arr;
    }

    /*
     * 周月循环传值
     * $uarr用户增长数组
     * $randArr时间范围
     */
    public function forWeekMonthYearArr($uarr, $randArr)
    {
        $countArr = [];
        for ($i = 0; $i < count($randArr); $i++) {
            if (array_key_exists($randArr[$i], $uarr)) {
                $countArr[] = $uarr[$randArr[$i]];
            } else {
                $countArr[] = 0;
            }
        }
        return ['time_arr' => $randArr, 'count_arr' => $countArr];
    }
}
