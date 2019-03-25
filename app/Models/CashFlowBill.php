<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CashFlowBill extends Model
{
    use SoftDeletes;
    
    const CASHTYPE = [
        '消费', '充值', '退款', '其他'
    ];
    const PAYWAY = [
        'Go小程序', 'APP', '到店取餐', '外卖', '其他'
    ];
    const TRADEWAY = [
        '喜茶钱包', '微信支付', '其他'
    ];
    const MEMBERTYPE = [
        'Go会员', '星球会员'
    ];
    
    
    protected $table = 'cash_flow_bills';
    protected $dates = ['deleted_at'];  //开启deleted_at
    protected $fillable = ['user_id', 'cash_type', 'cash_money', 'pay_way', 'trade_way', 'store_id', 'status', 'free_money', 'bill_no', 'payment', 
                           'active_money', 'member_type', 'created_at', 'updated_at'];
    
    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function shop() {
        return $this->belongsTo(Shop::class, 'store_id', 'id');
    }
    
    public function member(){
        return $this->belongsTo(Member::class,'user_id','user_id');
    }
}
