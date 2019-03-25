<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Repositories\Admin\CashFlowBillRepository;
use IQuery;
use Carbon\Carbon;
use App\Models\CashFlowBill;
use Maatwebsite\Excel\Excel;
/*
 * 储值流水单控制器
 */
class CashFlowBillController extends ApiController
{
    // redis存储路径
    const REDIS_PATH = 'laravel:coupon:';
    const STATUS = [
        '' => '全部',
        0 => '成功',
        1 => '失败'
    ];
    
    const PAYWAY = [
        '' => '全部',
        0 => 'Go小程序',
        1 => 'APP'
    ];
    
    /**
     * 储值账单流水
     */
    public function index(Request $request) {
        $rps = new CashFlowBillRepository();
        $cash_bills = $rps->index($request);
        return $this->response->collection($cash_bills);
    }
    
    /**
     * 用户详细金额
     */
    public function accountMoney(Request $request) {
        $rps = new CashFlowBillRepository();
        $account = $rps->accountMoney($request);
        return $account;
    }
    
    /**
     * 用户账单交易详情
     */
    public function billNoDetail(Request $request) {
        $rps = new CashFlowBillRepository();
        $res = $rps->billNoDetail($request);
        return $res;
    }
    
    /**
     * 导出-余额文件
     */
    public function excelBalance(Request $request) {
        $rps = new CashFlowBillRepository();
        $res = $rps->excelBalance($request);
        return $res;
    }
    
    /**
     * 导出excel表余额数据
     */
    public function outExcelBalance(Request $request, Excel $excel) {
        $sign = $request->sign;
        if (IQuery::redisGet(SELF::REDIS_PATH . 'sign') != $sign) {
            abort(404);
        }
        $res = $this->excelBalance($request);
        $storage = $res['storage'];
        $bills = $res['bills'];
        $user = $res['user'];
        if (ob_get_contents()) ob_end_clean();//清除缓冲区,避免乱码
        $excel->create('余额文件下载', function($e1) use($user, $storage, $bills) {
            $e1->sheet('Excel sheet', function($sheet) use($user, $storage, $bills) {
                $this->mergeCells($sheet, 12, [1, 2]);
                $sheet->row(1, ['用户姓名：'.$user['name']]);        // 操作第一行
                $sheet->row(2, ['手机号：'.$user['phone']]);        // 操作第二行
                $this->mergeCells($sheet, 12, [3, 6]);
                $sheet->row(3, ['余额文件(汇总)']);        // 操作第一行
                $this->mergeCells($sheet, 4, [4, 5]);
                $sheet->row(4, ['已充值金额','','','','已消费金额','','','','余额总数']);        // 操作第二行
                $sheet->row(5, [$storage['total_money'],'','','',$storage['consume_money'],'','','',$storage['free_money']]);        // 操作第二行
                $sheet->row(6, ['余额文件(汇总)']);        // 操作第四行
                $this->mergeCells($sheet, 3, [7]);
                $sheet->row(7, ['日期','','','交易金额','','','交易类型', '', '', '账户余额']);        // 操作第5行
                foreach($bills as $k => $bill) {
                    $this->mergeCells($sheet, 3, [8 + $k]);
                    $sheet->row($k + 8, [$bill['time'],'','',$bill['cash_money'],'','', CashFlowBill::CASHTYPE[$bill['cash_type']], '', '', $bill['free_money']]);
                }
                $this->mergeCells($sheet, 12, [$k + 9]);
                $sheet->row($k + 9, ['共导出文件数据：'.count($bills)]);        // 操作最后一行
                for($i = 3; $i<=8 + $k; $i++) {
                    $sheet->row($i, function($row){ 
                        $row->setAlignment('center');
                    });
                }
            });
        })->export('xls');
    }
    
    /**
     * 合并excel单元格
     * $sheet文本
     * $num步数
     * $b行数
     */
    public function mergeCells($sheet, $num, $bs) {
        $arr = ['0', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        foreach($bs as $b) {
            for($i = 0; $i < 12; $i+=$num) {
                $a = $i + 1;
                $sheet->mergeCells($arr[$a].$b.':'.$arr[$a+$num-1].$b);
            }
        }
    }

    /**
     * 充值数据统计页
     */
    public function rebillCount(Request $request) {
        $rps = new CashFlowBillRepository();
        $res = $rps->rebillCount($request);
        if (count($res) > 0) {
            foreach($res as $r) {
                $r->rebill_time = $request->rebill_start.'至'.$request->rebill_end;
                $r->status = self::STATUS[$request->status];
                $r->pay_way = self::PAYWAY[$request->pay_way];
            }
        }
        return $res;
    }
    
    /*
     * 导出充值数据excel表
     */
    public function outRebillCount(Request $request, Excel $excel) {
        $sign = $request->sign;
        if (IQuery::redisGet(SELF::REDIS_PATH . 'sign') != $sign) {
            abort(404);
        }
        $res = $this->rebillCount($request);
        if (ob_get_contents()) ob_end_clean();//清除缓冲区,避免乱码
        $excel->create('充值数据统计', function($e1) use($res) {
            $e1->sheet('Excel sheet', function($sheet) use($res) {
                $data = $res[0];
                $sheet->mergeCells('A1:G1');
                $sheet->row(1, ['充值数据统计（汇总）']); // 操作第一行
                $sheet->row(2, ['时间', '交易状态', '充值渠道', '充值总金额', '支付总金额', '充值总用户数', '充值总次数']); // 操作第二行
                $sheet->row(3, [$data->rebill_time, $data->status, $data->pay_way, $data->cash_money, $data->payment, $data->rebill_persion, $data->rebill_count]); // 操作第三行
                $sheet->setWidth([      // 设置多个列 
                    'A' => 40, 
                    'B' => 20,
                    'C' => 20, 
                    'D' => 20, 
                    'E' => 20, 
                    'F' => 20, 
                    'G' => 20
                ]); 
                for($i = 1; $i<=3; $i++) {
                    $sheet->row($i, function($row){ 
                        $row->setAlignment('center');
                    });
                }  
            });
        })->export('xls');
    }

     /**
     * 消费数据统计页
     */
    public function rebuyCount(Request $request) {
        $rps = new CashFlowBillRepository();
        $res = $rps->rebuyCount($request);
        if (count($res) > 0) {
            foreach($res as $r) {
                $r->rebuy_time = $request->rebuy_start.'至'.$request->rebuy_end;
                $r->status = self::STATUS[$request->status];
                $r->pay_way = self::PAYWAY[$request->pay_way];
            }
        }
        return $res;
    }
    
    /*
     * 导出消费数据excel表
     */
    public function outRebuyCount(Request $request, Excel $excel) {
        $sign = $request->sign;
        if (IQuery::redisGet(SELF::REDIS_PATH . 'sign') != $sign) {
            abort(404);
        }
        $res = $this->rebuyCount($request);
        if (ob_get_contents()) ob_end_clean();//清除缓冲区,避免乱码
        $excel->create('消费数据统计', function($e1) use($res) {
            $e1->sheet('Excel sheet', function($sheet) use($res) {
                $data = $res[0];
                $sheet->mergeCells('A1:G1');
                $sheet->row(1, ['交易数据统计（汇总）']); // 操作第一行
                $sheet->row(2, ['时间', '交易状态', '支付渠道', '交易总用户数', '交易总次数', '交易总金额']); // 操作第二行
                $sheet->row(3, [$data->rebuy_time, $data->status, $data->pay_way, $data->rebuy_persion, $data->rebuy_count, $data->cash_money]); // 操作第三行
                $sheet->setWidth([      // 设置多个列 
                    'A' => 40, 
                    'B' => 20,
                    'C' => 20, 
                    'D' => 20, 
                    'E' => 20, 
                    'F' => 20, 
                    'G' => 20
                ]); 
                for($i = 1; $i<=3; $i++) {
                    $sheet->row($i, function($row){ 
                        $row->setAlignment('center');
                    });
                }  
            });
        })->export('xls');
    }
}
