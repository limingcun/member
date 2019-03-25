<?php

namespace App\Http\Repositories\Admin;

use Illuminate\Http\Request;
use App\Models\CashFlowBill;
use App\Http\Repositories\BaseRepository;
use App\Http\Repositories\Admin\MemberRepository;
use App\Transformers\Admin\BillItemTransformer;
use App\Models\CashStorage;
use Carbon\Carbon;
use App\Models\User;
use DB;

class CashFlowBillRepository extends BaseRepository
{
    protected $table;

    public function __construct()
    {
        $this->table = new CashFlowBill();
    }

    public function index(Request $request)
    {
        $page = $request->page_size ?? config('app.page');
        $cash_bills = $this->table->where('user_id', $request->user_id);
        $cash_bills = $this->whereQuery($cash_bills, [
            'trade_start' => 'created_at',
            'trade_end' => 'created_at',
            'cash_type' => 'cash_type',
            'pay_way' => 'pay_way',
            'trade_way' => 'trade_way',
            'bill_no' => 'bill_no',
            'status' => 'status'
        ], $request);
        return $cash_bills->orderBy('created_at', 'desc')->paginate($page);
    }
    
    public function accountMoney(Request $request) {
        $user_id = $request->user_id;
        $sql = "select total_money,consume_money,free_money,active_money from cash_storages where user_id = ".$user_id;
        $res = DB::select($sql);
        return $res;
    }
    
    /**
     * 储值交易详情
     */
    public function billNoDetail(Request $request) {
        $no = $request->bill_no;
        $cash_type = $request->cash_type;
        if ($cash_type == 0 || $cash_type == 2) {
            $mrs = new MemberRepository();
            $res = $mrs->orderDetail('no', $no);
            $res = $this->discountPrice($res);
            if ($res->refund_status == 'FULL_REFUND' && $cash_type == 2) {
                $res->status_text = '已退款';
            } else {
                $res->status_text = '支付成功';
            }
        } else {
            $tran = new BillItemTransformer();
            $bill = CashFlowBill::with('user')->where('bill_no', $no)->first();
            $res = $tran->transform($bill);
        }
        return $res;
    }
    
    /**
     * 统计折扣金额
     * $res数组循环
     */
    public function discountPrice($res) {
        $items = $res->items;
        $discountPrice = 0.00;
        if (count($items) > 0) {
            foreach($items as $item) {
                $discountPrice += $item->discount_price;
            }
        }
        $res->discountPrice = number_format($discountPrice, 2);
        return $res;
    }
    
    /**
     * 导出余额文件
     * @param Request $request
     */
    public function excelBalance(Request $request) {
        $user_id = $request->user_id;
        $user = User::where('id', $user_id)->select('name', 'phone')->first();
        $bills = CashFlowBill::where('user_id', $user_id)->where('status', 0);
        if ($request->start_time != '' && $request->end_time != '') {
            $bills->whereDate('created_at', '>=', $request->start_time)->whereDate('created_at', '<=', $request->end_time);
        }
        $bills = $bills->select('created_at as time', 'cash_money', 'cash_type', 'free_money')->orderBy('created_at', 'desc')->get();
        $storage = $this->storage($bills);
        return compact('user', 'storage', 'bills');
    }
    
    /**
     * 统计余额数据
     * $bills账单
     */
    public function storage($bills) {
        $total_money = 0;
        $consume_money = 0;
        $free_money = 0;
        foreach($bills as $k => $bill) {
            if ($k == 0) {
                $free_money = $bill->free_money;
            }
            if ($bill->cash_type == 0) {
                $consume_money += $bill->cash_money;
            } else if ($bill->cash_type == 1) {
                $total_money += $bill->cash_money;
            } else if ($bill->cash_type == 2) {
                $consume_money -= $bill->cash_money;
            }
        }
        $storage['total_money'] = round($total_money, 2);
        $storage['consume_money'] = round($consume_money, 2);
        $storage['free_money'] = round($free_money, 2);
        return $storage;
    }

        /**
     * 充值数据统计
     * @param Request $request
     * @return type
     */
    public function rebillCount(Request $request) {
        if ($request->rebill_start != '' && $request->rebill_end != '') {
            $rebill_start = $request->rebill_start;
            $rebill_end = $request->rebill_end;
            $sql = "select count(u.id) as rebill_persion, IFNULL(sum(u.cash_money),0) as cash_money, IFNULL(sum(u.payment),0) as payment, IFNULL(sum(u.rebill_count),0) as rebill_count "
                 . "from (select id, (sum(cash_money) + sum(active_money)) as cash_money, sum(payment) as payment, count(id) as rebill_count, "
                 . "count(user_id) from cash_flow_bills where cash_type = 1 and status = 0 and DATE_FORMAT(created_at, '%Y-%m-%d') between '$rebill_start' and '$rebill_end'";
            if ($request->status != '') {
                $status = $request->status;
                $sql = $sql." and status = '$status'";
            }
            if ($request->pay_way != '') {
                $pay_way = $request->pay_way;
                $sql = $sql." and pay_way = '$pay_way'";
            }
            $sql = $sql. " group by user_id) u";
            $res = DB::select($sql);
            return $res;
        }
        return [];
    }
    
    /**
     * 消费统计数据
     * @param Request $request
     */
    public function rebuyCount(Request $request) {
        if ($request->rebuy_start != '' && $request->rebuy_end != '') {
            $rebuy_start = $request->rebuy_start;
            $rebuy_end = $request->rebuy_end;
            $sql = "select count(u.id) as rebuy_persion, IFNULL(sum(u.cash_money),0) as cash_money, IFNULL(sum(u.payment),0) as payment, IFNULL(sum(u.rebuy_count),0) as rebuy_count "
               . "from (select id, (sum(case when cash_type = 0 then cash_money else 0 end) - sum(case when cash_type = 2 then cash_money else 0 end)) as cash_money, sum(payment) as payment, count(id) as rebuy_count, "
               . "count(user_id) from cash_flow_bills where cash_type != 1 and status = 0 and DATE_FORMAT(created_at, '%Y-%m-%d') between '$rebuy_start' and '$rebuy_end'";
            if ($request->status != '') {
                $status = $request->status;
                $sql = $sql." and status = '$status'";
            }
            if ($request->pay_way != '') {
                $pay_way = $request->pay_way;
                $sql = $sql." and pay_way = '$pay_way'";
            }
            $sql = $sql. " group by user_id) u";
            $res = DB::select($sql);
            return $res;
        }
        return [];
    }
}
