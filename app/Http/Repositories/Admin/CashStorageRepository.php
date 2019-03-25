<?php

namespace App\Http\Repositories\Admin;

use Illuminate\Http\Request;
use App\Models\CashStorage;
use App\Http\Repositories\BaseRepository;
use Carbon\Carbon;
use DB;

class CashStorageRepository extends BaseRepository
{
    protected $table;

    public function __construct()
    {
        $this->table = new CashStorage();
    }

    public function index(Request $request)
    {
        $page = $request->page_size ?? config('app.page');
        $cash_storages = $this->table->where('total_money', '>', 0)
            ->with([
                'user',
                'member'
            ])->when($request->keyword, function ($query, $value) {
                $query->where(function($query) use($value) {
                    $query->whereIn('user_id', function($query) use($value) {
                        $query->select('id')->from('users')->where('id', $value)->orWhere('phone', $value);
                    });
                });
            })->when($request->sex, function ($query, $value) {
                $query->whereHas('member', function($query) use($value) {
                    if ($value != 'unknow') {
                        $query->where('sex', $value);
                    } else {
                        $query->whereIn('sex', ['unknow', '']);
                    }
                });
            });
        if ($request->member_type != '') {
            $member_type = $request->member_type;
            $cash_storages = $cash_storages->whereIn('user_id', function($query) use($member_type) {
                $query = $query->select('user_id')->from('members');
                if ($member_type) {
                    $query->whereNotNull('expire_time')->whereDate('expire_time', '>=', Carbon::today());
                } else {
                    $query->whereNull('expire_time')->orWhereDate('expire_time', '<', Carbon::today());
                }
            });
        }
        $cash_storages = $this->whereQuery($cash_storages, [
            'status' => 'status',
            'storage_start' => 'storage_start',
            'storage_end' => 'storage_start',
            'storage_way' => 'storage_way',
        ], $request);
        return $cash_storages->orderBy('id', 'desc')->paginate($page);
    }
    
    /**
     * 用户详情
     * @param type $id
     * @return type
     */
    public function show($id) {
        $cash_storage = $this->table->with('member')->find($id);
        return $cash_storage;
    }


    /*
     * 储值用户数量
     */
    public function storageNum()
    {
        $total_number = CashStorage::count();
        return response(compact('total_number'));
    }

    /*
     * 日用户数据增长点
     * $num是数量
     * $type是总用户还是星球会员
     */
    public function dateIncrease($num)
    {
        $randArr = [];
        for ($i = 1; $i <= 24; $i++) {
            if ($i < 10) {
                $c = '0' . $i;
            } else {
                $c = $i;
            }
            $randArr[] = $c . ':00';
        }
        $start = Carbon::yesterday()->startOfDay();
        $end = Carbon::yesterday()->endOfDay();
        $uarr = [];
        $sql = "SELECT DISTINCT(DATE_FORMAT(u.created_at,'%Y-%m-%d %H')) as time, count(1) as num, u.created_at as c_time from cash_storages as u where u.created_at BETWEEN '" .$start. "' and '" .$end. "' GROUP BY time";
        $res = DB::select($sql);
        foreach ($res as $r) {
            if ($r->c_time >= $start && $r->c_time < $end) {
                $timeArr = explode(' ', $r->time);
                $uarr[intval($timeArr[1])] = $r->num;
            }
        }
        $arr = $this->forUserStarArr($uarr, 0, $num - 1, $randArr);
        return $arr;
    }

    /*
     * 周或月用户数据增长点
     * $num是数量
     * $type是总用户还是星球会员
     * $t是周或月类型
     */
    public function weekOrMonthIncrease($num, $t)
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
        $sql = "SELECT DISTINCT(DATE_FORMAT(u.created_at,'%Y-%m-%d')) as time, count(1) as num from cash_storages as u where u.created_at BETWEEN '" .$start. "' and '" .$end. "' GROUP BY time";
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
    public function yearIncrease($num)
    {
        $randArr = [];
        $start = Carbon::today()->subYear(1);
        $end = Carbon::today()->subMonth(1);
        for ($i = 0; $i < 12; $i++) {
            $randArr[] = Carbon::today()->subYear(1)->addMonth($i)->format('Y-m');
        }
        $uarr = [];
        $sql = "SELECT DISTINCT(DATE_FORMAT(u.created_at,'%Y-%m')) as time, count(1) as num from cash_storages as u GROUP BY time";
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
     * 日循环传值
     * $uarr用户增长数组
     * $start开始循环值
     * $end结束循环值
     * $randArr时间范围
     */
    public function forUserStarArr($uarr, $start, $end, $randArr)
    {
        $countArr = [];
        for ($i = $start; $i <= $end; $i++) {
            if (array_key_exists($i, $uarr)) {
                $countArr[] = $uarr[$i];
            } else {
                $countArr[] = 0;
            }
        }
        return ['time_arr' => $randArr, 'count_arr' => $countArr];
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
    
    /**
     * 充值金额,交易金额,储值总金额
     */
    public function RechargeAmount() {
        $sql1 = "select sum(cs.total_money) as sum_total_money, sum(cs.consume_money) as sum_consume_money, sum(cs.free_money) as sum_free_money, "
              . "sum(case when m.sex = 'male' then cs.free_money else 0 end) as male_free_money, "
              . "sum(case when m.sex = 'female' then cs.free_money else 0 end) as female_free_money, "
              . "sum(case when m.sex = 'unknow' or sex = '' then cs.free_money else 0 end) as unknow_free_money "
              . "from cash_storages cs left join members m on cs.user_id = m.user_id where cs.deleted_at is null";
        $res1 = DB::select($sql1);
        $sql2 = "select sum(case when cb.cash_type = 1 and cb.member_type = 1 then cb.payment else 0 end) as star_total_money, "
              . "sum(case when cb.cash_type = 1 and cb.member_type = 0 then cb.payment else 0 end) as go_total_money, "
              . "(sum(case when cb.cash_type = 0 and cb.pay_way = 0 then cb.payment else 0 end) - sum(case when cb.cash_type = 2 and cb.pay_way = 0 then cb.payment else 0 end)) as wx_consume_money, "
              . "(sum(case when cb.cash_type = 0 and cb.pay_way = 1 then cb.payment else 0 end) - sum(case when cb.cash_type = 2 and cb.pay_way = 1 then cb.payment else 0 end)) as shop_consume_money "
              . "from cash_flow_bills cb where cb.deleted_at is null and cb.status = 0";
        $res2 = DB::select($sql2);
        foreach($res1 as $r1) {
            $total_money = $r1->sum_total_money ?? 0;
            $consume_money = $r1->sum_consume_money ?? 0;
            $free_money = $r1->sum_free_money ?? 0;
            $male_free_money = $r1->male_free_money ?? 0;
            $female_free_money = $r1->female_free_money ?? 0;
            $unknow_free_money = $r1->unknow_free_money ?? 0;
        }
        foreach($res2 as $r2) {
            $star_total_money = $r2->star_total_money ?? 0;
            $go_total_money = $r2->go_total_money ?? 0;
            $wx_consume_money = $r2->wx_consume_money ?? 0;
            $shop_consume_money = $r2->shop_consume_money ?? 0;
        }
        if ($total_money > 0) {
                $star_rate = round($star_total_money / $total_money * 100, 2).'%';
                $go_rate = round($go_total_money / $total_money * 100, 2).'%';
            } else {
                $star_rate = '0%';
                $go_rate = '0%';
            }
            if ($consume_money > 0) {
                $wx_rate = round($wx_consume_money / $consume_money * 100, 2).'%';
                $shop_rate = round($shop_consume_money / $consume_money * 100, 2).'%';
            } else {
                $wx_rate = '0%';
                $shop_rate = '0%';
            }
            if ($free_money > 0) {
                $male_rate = round($male_free_money / $free_money * 100, 2).'%';
                $female_rate = round($female_free_money / $free_money * 100, 2).'%';
                $unknow_rate = round($unknow_free_money / $free_money * 100, 2).'%';
            } else {
                $male_rate = '0%';
                $female_rate = '0%';
                $unknow_rate = '0%';
            }
        return response(compact('total_money', 'star_rate', 'go_rate', 'consume_money', 'wx_rate', 'shop_rate', 'free_money', 'male_rate', 'female_rate', 'unknow_rate'));  
    }
    
    /**
     * 用户累计充值金额,消费金额,余额
     */
    public function feeAddUp() {
        $sql = "select IFNULL(sum(total_money), 0) as sum_total_money, IFNULL(sum(consume_money), 0) as sum_consume_money, IFNULL(sum(free_money), 0) as sum_free_money, "
             . "IFNULL(sum(active_money), 0) as sum_active_money from cash_storages where deleted_at is null";
        $res = DB::select($sql);
        return $res;
    }
}
