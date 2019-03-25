<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Carbon\Carbon;
use DB;
use App\Models\CashStorage;
use App\Models\CashFlowBill;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Member;

class CashStorageController extends ApiController
{   
    const TRANSTYPE = [
        'refill' => 1,   //充值
        'cost' => 2,     //消费
        'refund_refill' => 3,   //充值退款
        'refund_cost' => 4,   //消费退款
        'revoke_cost' => 5    //消费撤销
    ];

    /**
     * 钱包账户
     */
    public function account(Request $request) {
        DB::beginTransaction();
        try {
            $data = $request->data;
            $data = json_decode(urldecode($data), true);
            \Log::info('bbbbbb', [$data]);
            $user_id = intval($data['userId']);
            $user = User::findOrFail($user_id);
            if (!$user) {
                return 'success=Y';
            }
            $status = $this->accountStatus($this->fiterStr($data['accountStatus']));
            $password_status = $this->passwordStatus($this->fiterStr($data['secretStatus']));
            $cash_storage = CashStorage::where('user_id', $user_id)->first();
            if (!$cash_storage) {
                CashStorage::create([
                    'user_id' => $user_id,
                    'account' => $this->fiterStr($data['accNo']),
                    'storage_start' => Carbon::parse($data['walletRegistTime'])->format('Y-m-d'),
                    'storage_way' => $this->fiterStr($data['registSource']),
                    'free_money' => round($this->fiterStr($data['balance']) / 100, 2),
                    'status' => $status,
                    'total_money' => round($this->fiterStr($data['balance']) / 100, 2),
                    'password_status' => $password_status,
                    'created_at' => Carbon::parse($data['walletRegistTime'])->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::parse($data['walletRegistTime'])->format('Y-m-d H:i:s')
                ]);
            } else {
                \Log::info('cccccc', [$password_status]);
                $cash_storage->status = $status;
                $cash_storage->password_status = $password_status;
                $cash_storage->account = $this->fiterStr($data['accNo']);
//                $cash_storage->free_money = round($this->fiterStr($data['balance']) / 100, 2);
                $cash_storage->save();
            }
            DB::commit();
            return 'success=Y';
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::info('CASH_STORAGE_ERROR', [$e]);
        }
    }

    /**
     * 钱包充值消费
     */
    public function rechargeConsume(Request $request) {
        DB::beginTransaction();
        try {
            $data = $request->data;
            $data = json_decode(urldecode($data), true);
            \Log::info('gggggg', [$data]);
            $user_id = intval($data['userId']);
            if ($user_id <= 0) {
                return 'success=Y';
            }
            $pay_way = $this->payWay($this->fiterStr($data['transSource']));  //交易方式
            $trade_way = $this->tradeWay($this->fiterStr($data['transChannel']));  //交易渠道
            $order_money = $this->fiterStr($data['orderAmount']);  //订单金额
            $payment = $this->fiterStr($data['realAmount']);  //实付金额
            $free_money = $this->fiterStr($data['balance']);    //余额
            $cash_type = $this->cashType($this->fiterStr($data['transType']));  //交易类型
            $active_money = $this->activeMoney($cash_type, $order_money, $payment);  //活动优惠金额
            $bill_no = $this->billNo($this->fiterStr($data['transType']), $this->fiterStr($data['serialNumber']), $this->fiterStr($data['mchOrderNo']), $data['oriMchOrderNo'] ?? '');
            $flow_bill = CashFlowBill::where('bill_no', $bill_no)->where('cash_type', $cash_type)->first();
            if ($flow_bill) {
                return 'success=Y';
            }
            if ($cash_type == 0) {    //如果是交易,判断订单有没有付款
                $order = Order::where('no', $bill_no)->whereNotNull('paid_at')->first();
                if (!$order) {
                    return 'success=N';
                }
            } else if ($cash_type == 2) {
                $f = CashFlowBill::where('bill_no', $bill_no)->where('cash_type', 0)->first();
                if (!$f) {
                    return 'success=N';
                }
            }
            $member_type = $this->isMemberType($user_id);
            $status = $this->transStatus($this->fiterStr($data['transStatus']));
            $trans_time = $data['transTime'];
            CashFlowBill::create([
                'account' => $this->fiterStr($data['accNo']),
                'user_id' => $user_id,
                'cash_type' => $cash_type,
                'cash_money' => round($this->fiterStr($data['realAmount']) / 100, 2), //订单金额
                'pay_way' => $pay_way,
                'trade_way' => $trade_way,
                'store_id' => $data['storeId'] ?? 0,
                'status' => $status,
                'free_money' => round($free_money / 100, 2),  //余额
                'bill_no' => $bill_no,
                'payment' => round($payment / 100, 2),   //实付金额
                'active_money' => round($active_money / 100, 2),
                'member_type' => $member_type,
                'created_at' => Carbon::parse($trans_time)->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::parse($trans_time)->format('Y-m-d H:i:s')
            ]);
            //保存账号数据信息
            if (!$status) {
                $account = $this->saveAccount($user_id, $active_money, $payment, $free_money, $cash_type, $trans_time);
                if (!$account) {
                    DB::rollBack();
                    \Log::info('CASH_ACCOUNT_ERROR', ['ERROR']);
                    return 'success=Y';
                }
            }
            DB::commit();
            return 'success=Y';
        } catch(\Exception $e) {
            DB::rollBack();
            \Log::info('CASH_BILL_ERROR', [$e]);
        }
    }
    
    /**
     * 更新账户数据信息
     * $active_money活动优惠金额
     * $payment实际支付金额
     * $cash_type支付类型(0消费,1充值,2退款)
     */
    public function saveAccount($user_id, $active_money, $payment, $free_money, $cash_type, $trans_time) {
        $cash_storage = CashStorage::where('user_id', $user_id)->first();
        if (!$cash_storage) {
            return false;
        }
        if ($cash_type == 0) {
            $cash_storage->consume_money += round($payment / 100, 2);
        } else if ($cash_type == 1) {
            $cash_storage->total_money += round($payment / 100 ,2);
        } else if ($cash_type == 2) {
            $cash_storage->consume_money -= round($payment / 100, 2);
        }
        $bill = CashFlowBill::where('user_id', $user_id)->orderBy('created_at', 'desc')->first();
        if ($bill) {
            if (Carbon::parse($bill->created_at)->timestamp <= Carbon::parse($trans_time)->timestamp) {
                $cash_storage->free_money = round($free_money / 100, 2);
            }
        } else {
            $cash_storage->free_money = round($free_money / 100, 2);
        }
        $cash_storage->active_money += round($active_money / 100, 2);
        $cash_storage->save();
        return true;
    }

    /**
     * 账户状态
     * @param type $accountStatus
     */
    public function accountStatus($accountStatus) {
        if ($accountStatus == 'AS01') {
            $status = 0;
        } else if ($accountStatus == 'AS02') {
            $status = 2;
        } else if ($accountStatus == 'AS00') {
            $status = 1;
        } else {
            $status = 0;
        }
        return $status;
    }
    
    /**
     * 密码状态
     * @param type $passwordStatus
     */
    public function passwordStatus($passwordStatus) {
        if ($passwordStatus == 'SS00') {
            $password_status = 0;
        } else if ($passwordStatus == 'SS01') {
            $password_status = 1;
        }
        return $password_status;
    }
    
    /**
     * 交易成功
     * $transStatus订单状态
     */
    public function transStatus($transStatus) {
        if ($transStatus == 2) {
            $status = 1;
        } else {
            $status = 0;
        }
        return $status;
    }

    /**
     * 交易方式
     * $transSource
     */
    public function payWay($transSource) {
        if ($transSource == 2) {
            $pay_way = 1;
        } else {
            $pay_way = 0;
        }
        return $pay_way;
    }
    
    /**
     * 交易渠道
     */
    public function tradeWay($transChannel) {
        if ($transChannel == 'WeChat') {  //微信支付
            $trade_way = 1;
        } else if ($transChannel == 'AliPay') {  //支付宝支付
            $trade_way = 2;
        } else if ($transChannel == 'GiftCard') {  //礼品卡支付
            $trade_way = 3;
        } else if ($transChannel == 'EntityCard') {  //实体卡支付
            $trade_way = 4;
        } else if ($transChannel == 'Account') {  //喜茶钱包支付
            $trade_way = 0;
        } else {
            $trade_way = 6;    //其他支付
        }
        return $trade_way;
    }
    
    /**
     * 交易类型
     */
    public function cashType($transType) {
        if ($transType == 1) {  //充值
            $cash_type = 1;
        } else if ($transType == 2) {  //消费
            $cash_type = 0;
        } else if ($transType == 3) {  //充值退款
            $cash_type = 3;
        } else if ($transType == 4) {  //消费退款
            $cash_type = 2;
        } else if ($transType == 5) {  //消费撤销
            $cash_type = 4;
        } else {
            $cash_type = 5;    //其他类型
        }
        return $cash_type;
    }
    
    /**
     * 判断是用实付金额还是订单金额
     * $transType交易类型
     * $orderAmount订单金额
     * $realAmount实际支付金额
     */
    public function activeMoney($transType, $orderAmount, $realAmount) {
        if ($transType == 1) {
            $active_money = round($orderAmount - $realAmount, 2);
        } else {
            $active_money = 0;
        }
        return $active_money;
    }
    
    /**
     * 交易订单号
     * @param type $transType
     * @param type $serialNumber
     * @param type $mchOrderNo
     * @return type
     */
    public function billNo($transType, $serialNumber, $mchOrderNo, $oriMchOrderNo = '') {
        if ($transType == 1 || $transType == 3) {
            $bill_no = $serialNumber;
        } else if ($transType == 2) {
            $bill_no = $mchOrderNo;
        } else if ($transType == 4) {
            $bill_no = $oriMchOrderNo;
        }
        return $bill_no;
    }
    
    /**
     * 去除特殊字符
     * @return type
     */
    public function fiterStr($str) {
        if (!get_magic_quotes_gpc()) { // 判断magic_quotes_gpc是否为打开
           $str = addslashes($str);
        }
        $str = str_replace('*', '', $str); // 把'*'过滤掉     
        $str = str_replace(';', '', $str); // 把';'过滤掉   
        $str = str_replace('%', '', $str); // 把'%'过滤掉
        $str = str_replace('\\', '', $str); // 把'\'过滤掉
        $str = str_replace('/', '', $str); // 把'/'过滤掉
        $str = nl2br($str); //回车转换    
        $str = htmlspecialchars($str); // html标记转换
        $str = stripslashes($str); //去掉转义字符
        return $str;
    }
    
    /**
     * 判断是星球会员还是go会员
     * $user_id用户id
     */
    public function isMemberType($user_id) {
        $member = Member::where('user_id', $user_id)->first();
        if (!$member->expire_time) {
            $member_type = 0;
        } else {
            if (Carbon::parse($member->expire_time)->timestamp >= Carbon::today()->timestamp) {
                $member_type = 1;
            } else {
                $member_type = 0;
            }
        }
        return $member_type;
    }
}
