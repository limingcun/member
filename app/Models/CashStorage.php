<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CashStorage extends Model
{
    use SoftDeletes;
    const STORAGEWAY = [
        'Go小程序', 'APP', '门店'
    ];
    const STATUS = [
        '正常', '已禁用', '已锁定'
    ];
    protected $table = 'cash_storages';
    protected $dates = ['deleted_at'];  //开启deleted_at
    protected $fillable = ['user_id', 'storage_start', 'storage_way', 'status', 'consume_way', 'total_money', 'consume_money', 'free_money', 
                           'account', 'password_status', 'active_money', 'created_at', 'updated_at'];

    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function member(){
        return $this->belongsTo(Member::class,'user_id','user_id');
    }
}
