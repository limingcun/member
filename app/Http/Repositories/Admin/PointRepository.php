<?php

namespace App\Http\Repositories\Admin;

use Illuminate\Http\Request;
use App\Models\MemberScore;
use App\Models\Member;
use App\Http\Repositories\BaseRepository;
use Carbon\Carbon;
use DB;

class PointRepository extends BaseRepository
{
    public function __construct()
    {
    }

    public function index(Request $request)
    {
        $page_size = $request->page_size ?? config('app.page'); //页码
        $page = $request->page ?? 1; //当前页
        $total = $this->queryMember(new Member(), $request)->count();
        $total_pages = floor($total/$page_size) + 1;  //总页数
        $per = ($page - 1) * $page_size;
        $members = Member::offset($per)->limit($page_size)->select(array_merge(Member::$score));
        $members = $this->queryMember($members, $request);
        $members = $members->get();
        return $this->burster($members, $page, $page_size, $total);
    }
    
    public function queryMember($query, $request) {
        $query = $query
            ->when(request('keyword'), function ($query, $value) {
                $query->where(function($query) use($value) {
                    $query->where('user_id', $value)->orWhere('phone', $value);
                });
            });
        $query = $this->whereQuery($query, [
            'order_money_min' => 'order_money',
            'order_money_max' => 'order_money',
            'order_min' => 'order_score',
            'order_max' => 'order_score',
            'usable_min' => 'usable_score',
            'usable_max' => 'usable_score',
            'used_min' => 'used_score',
            'used_max' => 'used_score'
        ], $request);
        return $query;
    }


    public function total()
    {
        $sql = "select sum(order_money) as sum_order_money, sum(order_score) as sum_order_score, sum(used_score) as sum_used_score, "
             . "sum(usable_score) as sum_usable_score from members";
        $res = DB::select($sql);
        $total = [];
        foreach($res as $r) {
            $total['order_money'] = $r->sum_order_money;
            $total['order_score'] = $r->sum_order_score;
            $total['used_score'] = $r->sum_used_score;
            $total['usable_score'] = $r->sum_usable_score;
        }
        return $total;
    }
    
    public function detail(Request $request) {
        $page_size = $request->page_size ?? config('app.page'); //页码
        $page = $request->page ?? 1; //当前页
        $member_score = new MemberScore();
        $total = $this->queryWhen(new MemberScore(), $request)->count();  //总数
        $total_pages = floor($total/$page_size) + 1;  //总页数
        $per = ($page - 1) * $page_size;
        $scores = MemberScore::offset($per)->limit($page_size)->orderBy('id', 'desc');
        $scores = $this->queryWhen($scores, $request);
        $scores = $scores->get();
        return $this->burster($scores, $page, $page_size, $total);
    }
    
    /*
     * 数据条件查询
     */
    public function queryWhen($query, $request) {
        if ($request->keyword != '') {
            $value = $request->keyword;
            $query = $query->where(function($query) use($value) {
                $query->whereIn('user_id', function($query) use($value) {
                    $query->select('id')->from('users')->where('phone', $value);
                })->orWhereIn('source_id', function($query) use($value) {
                    $query->select('id')->from('orders')->where('no', $value);
                })->orWhereIn('source_id', function($query) use($value) {
                    $query->select('id')->from('mall_orders')->where('no', $value);
                })->orWhereIn('source_id', function($query) use($value) {
                    $query->select('id')->from('member_card_records')->where('order_no', $value);
                });
            });
        }
        $query = $query->when($request->change_start, function($query, $value) {
            $query->where('created_at', '>=', $value);
        })
        ->when($request->change_end, function($query, $value) {
            $query->where('created_at', '<=', $value);
        })
        ->when($request->method, function ($query, $value) {
            $query->where('method', $value);
        })
        ->when($request->user_id, function ($query, $value) {
            $query->where('user_id', $value);
        });
        return $query;
    }
}
