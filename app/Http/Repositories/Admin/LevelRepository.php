<?php

namespace App\Http\Repositories\Admin;

use Illuminate\Http\Request;
use App\Models\Level;
use App\Http\Repositories\BaseRepository;
use Carbon\Carbon;
use DB;

class LevelRepository extends BaseRepository
{
    protected $table;

    public function __construct()
    {
        $this->table = new Level();
    }

    public function index(Request $request)
    {

    }

    /**
     * go会员等级列表
     */
    public function levelList($levels)
    {
        foreach ($levels as $level) {
            $level['equity'] = $this->equity($level['name']);
        }
        return $levels;
    }

    /**
     * go会员单个等级权益说明
     */
    public function equity($level)
    {
        $level = substr($level->name, 2);
        $arr = [];
        switch ($level) {
            case $level < 5:
                $cash = [3, 120, 5];
                $fee = [2, 6, 1];
                $arr = $this->getEquityText(1, 4, $cash, $fee);
                break;
            case $level < 10:
                $cash = [3, 110, 10];
                $fee = [2, 5, 1];
                $arr = $this->getEquityText(5, 9, $cash, $fee);
                break;
            case $level < 15:
                $cash = [3, 110, 15];
                $fee = [3, 4, 1];
                $arr = $this->getEquityText(10, 14, $cash, $fee);
                break;
            case $level < 20:
                $cash = [3, 100, 15];
                $fee = [3, 3, 1];
                $arr = $this->getEquityText(15, 19, $cash, $fee);
                break;
            case $level < 25:
                $cash = [3, 120, 20];
                $fee = [3, 3, 1];
                $arr = $this->getEquityText(20, 24, $cash, $fee);
                break;
            case $level < 31:
                $cash = [3, 100, 20];
                $fee = [3, 2, 1];
                $arr = $this->getEquityText(25, 30, $cash, $fee);
                break;
            default:
                break;
        }
        return $arr;
    }

    public function getEquityText($level_l, $level_r, $cash, $fee)
    {
        $arr['title'] = "升级好礼（Lv{$level_l} ~ Lv{$level_r}）";
        $arr['body'] = [
            "{$cash[0]}张满{$cash[1]}元减{$cash[2]}元的满减券",
            "{$fee[0]}张满{$fee[1]}杯赠{$fee[2]}杯的赠饮券"
        ];
        return $arr;
    }

    /*
     * 周或月用户数据增长点
     * $num是数量
     * $type是总用户还是星球会员
     * $t是周或月类型
     */
    public function weekOrMonthIncrease($t, $level_id = '')
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
        $sql = "SELECT DISTINCT(DATE_FORMAT(m.created_at,'%Y-%m-%d')) as time, count(1) as num from members as m where m.created_at BETWEEN '" .$start. "' and '" .$end. "'";
        if ($level_id != '') {
            $sql = $sql . " and level_id = " . $level_id;
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
    public function yearIncrease($level_id = '')
    {
        $randArr = [];
        $start = Carbon::today()->subYear(1);
        $end = Carbon::today()->subMonth(1);
        for ($i = 0; $i < 12; $i++) {
            $randArr[] = Carbon::today()->subYear(1)->addMonth($i)->format('Y-m');
        }
        $uarr = [];
        $sql = "SELECT DISTINCT(DATE_FORMAT(m.created_at,'%Y-%m')) as time, count(1) as num from members as m";
        if ($level_id != '') {
            $sql = $sql . " where level_id = " . $level_id;
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
    
    /*
     * 会员等级比例
     */
    public function levelRatia()
    {
        //直接查go性能太差,使用反选查询
        $sql = 'select m.level_id, (count(m.level_id) - IFNULL(c.star_count, 0)) as member_count from members as m LEFT JOIN 
               (select level_id, count(level_id) as star_count from members where expire_time >= DATE(now()) group by level_id) 
                as c on m.level_id = c.level_id group by m.level_id';
        $res = DB::select($sql);
        $data = Level::get();
        $member_total = array_sum(array_column($res, 'member_count'));
        foreach($data as $level) {
            $flag = 0;
            foreach($res as $r) {
                if ($level->id == $r->level_id) {
                    $level->member_count = $r->member_count;
                    $flag = 1;
                }
            }
            if (!$flag) {
                $level->member_count = 0;
            }
        }
        return compact('member_total', 'data');
    }
}
